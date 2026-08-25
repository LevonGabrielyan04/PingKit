#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

read_env() {
    local key="$1"
    local default="${2:-}"

    if [[ ! -f .env ]]; then
        echo "$default"
        return
    fi

    local value
    value="$(grep -E "^${key}=" .env | tail -n 1 | cut -d= -f2- | tr -d '\r' | sed -e 's/^"//' -e 's/"$//')"
    echo "${value:-$default}"
}

resolve_command() {
    local cmd="$1"

    if command -v "$cmd" >/dev/null 2>&1; then
        command -v "$cmd"
        return 0
    fi

    if [[ -n "${SUDO_USER:-}" ]]; then
        local user_home
        user_home="$(getent passwd "$SUDO_USER" | cut -d: -f6)"

        if [[ -f "${user_home}/.nvm/nvm.sh" ]]; then
            export NVM_DIR="${user_home}/.nvm"
            # shellcheck source=/dev/null
            source "${user_home}/.nvm/nvm.sh"

            if command -v "$cmd" >/dev/null 2>&1; then
                command -v "$cmd"
                return 0
            fi
        fi

        local resolved
        resolved="$(sudo -u "$SUDO_USER" -H bash -lc "command -v ${cmd}" 2>/dev/null || true)"

        if [[ -n "$resolved" && -x "$resolved" ]]; then
            echo "$resolved"
            return 0
        fi
    fi

    return 1
}

if [[ "${EUID:-$(id -u)}" -eq 0 && -n "${SUDO_USER:-}" ]]; then
    echo "Warning: running as root via sudo."
    echo "Use ./dev-remote.sh without sudo for normal development."
    echo "Only use sudo if masscan jobs need NET_RAW/NET_ADMIN on the queue worker."
    echo ""
fi

NPM="$(resolve_command npm || true)"
if [[ -z "$NPM" ]]; then
    echo "npm not found." >&2
    echo "Run without sudo: ./dev-remote.sh" >&2
    echo "Or install Node.js (nvm) for user ${SUDO_USER:-$USER}." >&2
    exit 1
fi

HOST="$(read_env SERVER_HOST "0.0.0.0")"
PORT="$(read_env SERVER_PORT "8000")"
APP_URL="$(read_env APP_URL "http://${HOST}:${PORT}")"

pids=()

cleanup() {
    for pid in "${pids[@]}"; do
        kill "$pid" 2>/dev/null || true
    done

    wait 2>/dev/null || true
}

trap cleanup EXIT INT TERM

echo "Starting Laravel on http://${HOST}:${PORT}"
php artisan serve --host="$HOST" --port="$PORT" &
pids+=($!)

echo "Starting Vite dev server"
"$NPM" run dev &
pids+=($!)

QUEUE_CONNECTION="$(read_env QUEUE_CONNECTION "redis")"
MASSCAN_QUEUE="$(read_env MASSCAN_QUEUE "masscan")"

echo "Starting Laravel queue worker (${QUEUE_CONNECTION}) for queue '${MASSCAN_QUEUE}'"
php artisan queue:work "${QUEUE_CONNECTION}" \
    --queue="${MASSCAN_QUEUE}" \
    --sleep=3 \
    --tries=3 \
    --timeout=3600 \
    --max-time=3600 \
    --no-interaction &
pids+=($!)

# --- NEW COMMANDS ADDED HERE ---

echo "Starting Laravel queue worker for queue 'Polls'"
php artisan queue:work --queue=Polls &
pids+=($!)

echo "Starting Laravel scheduler"
php artisan schedule:work &
pids+=($!)

# -------------------------------

echo ""
echo "Remote access: ${APP_URL}"
echo "Press Ctrl+C to stop all processes."
echo ""

wait

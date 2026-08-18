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
npm run dev &
pids+=($!)

echo ""
echo "Remote access: ${APP_URL}"
echo "Press Ctrl+C to stop both servers."
echo ""

wait

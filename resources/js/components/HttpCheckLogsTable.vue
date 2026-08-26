<script setup lang="ts">
import { ref, watch } from 'vue';

export type HttpCheckLogRow = {
    id: string;
    target: string;
    created_at: string;
    status_code: number;
    response_time_ms: number;
    dns_time_ms: number | null;
    tcp_time_ms: number | null;
    tls_time_ms: number | null;
    error_message: string | null;
};

type Props = {
    logs?: HttpCheckLogRow[];
};

const props = withDefaults(defineProps<Props>(), {
    logs: () => [],
});

const selectedErrorMessage = ref<string | null>(null);

watch(selectedErrorMessage, (message, _previous, onCleanup) => {
    if (message === null) {
        return;
    }

    function onKeydown(event: KeyboardEvent): void {
        if (event.key === 'Escape') {
            closeErrorMessage();
        }
    }

    window.addEventListener('keydown', onKeydown);
    onCleanup(() => window.removeEventListener('keydown', onKeydown));
});

function formatNullableMs(value: number | null): string {
    if (value === null) {
        return '—';
    }

    return `${value} ms`;
}

function formatCheckedAt(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString();
}

function openErrorMessage(message: string): void {
    selectedErrorMessage.value = message;
}

function closeErrorMessage(): void {
    selectedErrorMessage.value = null;
}
</script>

<template>
    <div>
        <div
            v-if="props.logs.length === 0"
            class="nb-card default"
            data-test="http-check-logs-empty"
        >
            <div class="nb-card-content">
                <p class="nb-card-text">No failed HTTP check logs yet.</p>
            </div>
        </div>

        <div
            v-else
            class="nb-table-container orange overflow-x-auto"
            data-test="http-check-logs-list"
        >
            <table class="nb-table orange bordered">
                <thead>
                    <tr>
                        <th scope="col">Checked at</th>
                        <th scope="col">Target</th>
                        <th scope="col">Status</th>
                        <th scope="col">Response</th>
                        <th scope="col">DNS</th>
                        <th scope="col">TCP</th>
                        <th scope="col">TLS</th>
                        <th scope="col">Error</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="log in props.logs"
                        :key="log.id"
                        data-test="http-check-log-item"
                    >
                        <td>{{ formatCheckedAt(log.created_at) }}</td>
                        <td>{{ log.target }}</td>
                        <td>{{ log.status_code }}</td>
                        <td>{{ log.response_time_ms }} ms</td>
                        <td>{{ formatNullableMs(log.dns_time_ms) }}</td>
                        <td>{{ formatNullableMs(log.tcp_time_ms) }}</td>
                        <td>{{ formatNullableMs(log.tls_time_ms) }}</td>
                        <td>
                            <button
                                v-if="log.error_message"
                                type="button"
                                class="nb-button orange"
                                data-test="http-check-log-error-button"
                                @click="openErrorMessage(log.error_message)"
                            >
                                Message
                            </button>
                            <template v-else>—</template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="selectedErrorMessage !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            data-test="http-check-log-error-dialog"
            @click.self="closeErrorMessage"
        >
            <div
                class="nb-dialog orange"
                role="dialog"
                aria-modal="true"
                aria-labelledby="http-check-log-error-title"
            >
                <div
                    id="http-check-log-error-title"
                    class="nb-dialog-header"
                >
                    Message
                </div>
                <div class="nb-dialog-body whitespace-pre-wrap break-words">
                    {{ selectedErrorMessage }}
                </div>
                <div class="nb-dialog-footer">
                    <button
                        type="button"
                        class="nb-button orange"
                        data-test="http-check-log-error-hide"
                        @click="closeErrorMessage"
                    >
                        Hide
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

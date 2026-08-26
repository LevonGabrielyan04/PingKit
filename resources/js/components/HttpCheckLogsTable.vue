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
    response_headers: Record<string, unknown> | null;
};

type SelectedResponse = {
    errorMessage: string | null;
    responseHeaders: Record<string, unknown> | null;
};

type Props = {
    logs?: HttpCheckLogRow[];
};

const props = withDefaults(defineProps<Props>(), {
    logs: () => [],
});

const selectedResponse = ref<SelectedResponse | null>(null);

watch(selectedResponse, (response, _previous, onCleanup) => {
    if (response === null) {
        return;
    }

    function onKeydown(event: KeyboardEvent): void {
        if (event.key === 'Escape') {
            closeResponse();
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

function formatResponseHeaders(
    headers: Record<string, unknown> | null,
): string {
    if (headers === null || Object.keys(headers).length === 0) {
        return '—';
    }

    return JSON.stringify(headers, null, 2);
}

function hasResponseDetails(log: HttpCheckLogRow): boolean {
    if (log.error_message) {
        return true;
    }

    return (
        log.response_headers !== null &&
        Object.keys(log.response_headers).length > 0
    );
}

function openResponse(log: HttpCheckLogRow): void {
    selectedResponse.value = {
        errorMessage: log.error_message,
        responseHeaders: log.response_headers,
    };
}

function closeResponse(): void {
    selectedResponse.value = null;
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
            class="nb-table-container blue overflow-x-auto"
            data-test="http-check-logs-list"
        >
            <table class="nb-table blue bordered">
                <thead>
                    <tr>
                        <th scope="col">Checked at</th>
                        <th scope="col">Target</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
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
                            <a
                                v-if="hasResponseDetails(log)"
                                href="#"
                                class="font-medium text-black underline underline-offset-2"
                                data-test="http-check-log-error-button"
                                @click.prevent="openResponse(log)"
                            >
                                Response
                            </a>
                            <template v-else>—</template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="selectedResponse !== null"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4"
            data-test="http-check-log-error-dialog"
            @click.self="closeResponse"
        >
            <div
                class="nb-dialog blue max-h-[calc(100vh-2rem)] w-full"
                role="dialog"
                aria-modal="true"
                aria-labelledby="http-check-log-error-title"
            >
                <div
                    id="http-check-log-error-title"
                    class="nb-dialog-header shrink-0"
                >
                    Response
                </div>
                <div
                    class="nb-dialog-body min-h-0 flex-1 overflow-y-auto break-words"
                >
                    <div class="flex flex-col gap-4">
                        <section>
                            <h3 class="mb-2 font-semibold">Message</h3>
                            <p class="whitespace-pre-wrap">
                                {{ selectedResponse.errorMessage ?? '—' }}
                            </p>
                        </section>
                        <section>
                            <h3 class="mb-2 font-semibold">Headers</h3>
                            <pre
                                class="whitespace-pre-wrap"
                            >{{ formatResponseHeaders(selectedResponse.responseHeaders) }}</pre>
                        </section>
                    </div>
                </div>
                <div class="nb-dialog-footer shrink-0">
                    <button
                        type="button"
                        class="nb-button blue"
                        data-test="http-check-log-error-hide"
                        @click="closeResponse"
                    >
                        Hide
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
export type HttpCheckLogRow = {
    id: string;
    monitor_id: string;
    created_at: string;
    status_code: number;
    is_successful: boolean;
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
</script>

<template>
    <div>
        <div
            v-if="props.logs.length === 0"
            class="nb-card default"
            data-test="http-check-logs-empty"
        >
            <div class="nb-card-content">
                <p class="nb-card-text">No HTTP check logs yet.</p>
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
                        <th scope="col">Monitor</th>
                        <th scope="col">Status</th>
                        <th scope="col">Result</th>
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
                        <td class="font-mono text-xs">{{ log.monitor_id }}</td>
                        <td>{{ log.status_code }}</td>
                        <td>
                            {{ log.is_successful ? 'Successful' : 'Failed' }}
                        </td>
                        <td>{{ log.response_time_ms }} ms</td>
                        <td>{{ formatNullableMs(log.dns_time_ms) }}</td>
                        <td>{{ formatNullableMs(log.tcp_time_ms) }}</td>
                        <td>{{ formatNullableMs(log.tls_time_ms) }}</td>
                        <td>{{ log.error_message ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

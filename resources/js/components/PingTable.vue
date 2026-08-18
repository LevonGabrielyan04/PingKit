<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

type PingRow = {
    host: string;
    reachability?: 'online' | 'offline' | null;
    ipAddress?: string | null;

    latencyMinMs?: number | null;
    latencyAvgMs?: number | null;
    latencyMaxMs?: number | null;

    packetLossPercent?: number | null;

    ttl?: number | null;

    packetSizeBytes?: number | null;
};

type Props = {
    rows?: PingRow[];
};

const props = withDefaults(defineProps<Props>(), {
    rows: () => [],
});

function formatValue<T>(
    value: T | null | undefined,
    format: (v: T) => string,
): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return format(value);
}

function formatReachability(reachability: PingRow['reachability']): {
    label: string;
    className: string;
} {
    if (reachability === 'online') {
        return {
            label: 'Online',
            className: 'text-emerald-700 dark:text-emerald-400',
        };
    }

    if (reachability === 'offline') {
        return {
            label: 'Offline',
            className: 'text-rose-700 dark:text-rose-400',
        };
    }

    return { label: '—', className: 'text-neutral-500' };
}

function formatLatency(row: PingRow): string {
    const min = formatValue(row.latencyMinMs, (v) => `${v}`);
    const avg = formatValue(row.latencyAvgMs, (v) => `${v}`);
    const max = formatValue(row.latencyMaxMs, (v) => `${v}`);

    if (min === '—' && avg === '—' && max === '—') {
        return '—';
    }

    return `min ${min} / avg ${avg} / max ${max} ms`;
}

function formatPercent(value: number | null | undefined): string {
    return formatValue(value, (v) => `${v.toFixed(2)}%`);
}

function formatByteSize(value: number | null | undefined): string {
    return formatValue(value, (v) => `${v} bytes`);
}

const columnDescriptions = {
    hostReachability:
        'Confirms whether a destination device (website, server, or local IP) is online and responding.',
    ipResolution:
        'Converts domain names (e.g., google.com) into their numerical IPv4 or IPv6 address.',
    latency:
        'Measures the time in milliseconds (ms) it takes for data to travel to the target and back, showing minimum, maximum, and average response times.',
    packetLoss:
        'Reports the percentage of dropped packets, indicating network instability, congestion, or hardware issues.',
    ttl: 'Shows how many network hops (routers) a packet can pass through before being discarded, which gives a rough idea of the path length.',
    bufferPacketSize:
        'Displays the size of the ICMP payload sent (typically 32 bytes on Windows, 56/64 bytes on macOS/Linux).',
} as const;
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <div class="w-full overflow-x-auto">
            <table
                class="min-w-[900px] w-full border-collapse text-left text-sm"
            >
                <thead>
                    <tr class="text-neutral-600">
                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    Host Reachability
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{ columnDescriptions.hostReachability }}
                                </TooltipContent>
                            </Tooltip>
                        </th>

                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    IP Address Resolution
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{ columnDescriptions.ipResolution }}
                                </TooltipContent>
                            </Tooltip>
                        </th>

                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    Latency (Round-Trip Time / RTT)
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{ columnDescriptions.latency }}
                                </TooltipContent>
                            </Tooltip>
                        </th>

                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    Packet Loss
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{ columnDescriptions.packetLoss }}
                                </TooltipContent>
                            </Tooltip>
                        </th>

                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    Time to Live (TTL)
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{ columnDescriptions.ttl }}
                                </TooltipContent>
                            </Tooltip>
                        </th>

                        <th scope="col" class="px-3 py-2 font-semibold">
                            <Tooltip>
                                <TooltipTrigger>
                                    Buffer/Packet Size
                                </TooltipTrigger>
                                <TooltipContent>
                                    {{
                                        columnDescriptions.bufferPacketSize
                                    }}
                                </TooltipContent>
                            </Tooltip>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="props.rows.length === 0">
                        <td
                            colspan="6"
                            class="px-3 py-8 text-center text-neutral-500"
                        >
                            No ping results yet.
                        </td>
                    </tr>

                    <tr
                        v-for="row in props.rows"
                        :key="row.host"
                        class="border-t border-neutral-200"
                    >
                        <td class="px-3 py-3">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium">
                                    {{ row.host }}
                                </span>
                                <span
                                    :class="
                                        formatReachability(row.reachability)
                                            .className
                                    "
                                >
                                    {{
                                        formatReachability(row.reachability)
                                            .label
                                    }}
                                </span>
                            </div>
                        </td>

                        <td class="px-3 py-3">
                            {{ row.ipAddress ?? '—' }}
                        </td>

                        <td class="px-3 py-3 font-medium">
                            {{ formatLatency(row) }}
                        </td>

                        <td class="px-3 py-3">
                            {{ formatPercent(row.packetLossPercent) }}
                        </td>

                        <td class="px-3 py-3">
                            {{ formatValue(row.ttl, (v) => `${v} hops`) }}
                        </td>

                        <td class="px-3 py-3">
                            {{ formatByteSize(row.packetSizeBytes) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </TooltipProvider>
</template>


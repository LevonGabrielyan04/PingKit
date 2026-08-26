<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HttpCheckLogsTable, {
    type HttpCheckLogRow,
} from '@/components/HttpCheckLogsTable.vue';
import { http } from '@/routes';

withDefaults(
    defineProps<{
        logs?: HttpCheckLogRow[];
    }>(),
    {
        logs: () => [],
    },
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Http',
                href: http(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Http" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <HttpCheckLogsTable :logs="logs" />
    </div>
</template>

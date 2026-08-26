<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HttpCheckLogsTable, {
    type HttpCheckLogRow,
} from '@/components/HttpCheckLogsTable.vue';
import Pagination from '@/components/Pagination.vue';
import { usePageHref } from '@/composables/usePageHref';
import { http } from '@/routes';

type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

withDefaults(
    defineProps<{
        logs?: HttpCheckLogRow[];
        pagination?: PaginationMeta;
    }>(),
    {
        logs: () => [],
        pagination: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
        }),
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

const { pageHref } = usePageHref(http);
</script>

<template>
    <Head title="Http" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <HttpCheckLogsTable :logs="logs" />

        <Pagination
            :current-page="pagination.current_page"
            :last-page="pagination.last_page"
            :page-href="pageHref"
            aria-label="HTTP check logs pagination"
            test-id="http-check-logs"
        />
    </div>
</template>

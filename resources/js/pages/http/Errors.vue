<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HttpCheckLogsTable, {
    type HttpCheckLogRow,
} from '@/components/HttpCheckLogsTable.vue';
import HttpNavbar from '@/components/HttpNavbar.vue';
import Pagination from '@/components/Pagination.vue';
import { usePageHref } from '@/composables/usePageHref';
import { errors } from '@/routes/http';

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
                href: errors(),
            },
        ],
    },
});

const { pageHref } = usePageHref(errors);
</script>

<template>
    <Head title="Http Errors" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <HttpNavbar />

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

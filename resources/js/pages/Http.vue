<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import HttpCheckLogsTable, {
    type HttpCheckLogRow,
} from '@/components/HttpCheckLogsTable.vue';
import { http } from '@/routes';

type Pagination = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

const props = withDefaults(
    defineProps<{
        logs?: HttpCheckLogRow[];
        pagination?: Pagination;
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

const hasPages = computed(() => props.pagination.last_page > 1);

const previousPageHref = computed(() => {
    const page = props.pagination.current_page - 1;

    if (page <= 1) {
        return http();
    }

    return http({ query: { page } });
});

const nextPageHref = computed(() =>
    http({ query: { page: props.pagination.current_page + 1 } }),
);
</script>

<template>
    <Head title="Http" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <HttpCheckLogsTable :logs="logs" />

        <nav
            v-if="hasPages"
            class="flex flex-wrap items-center justify-between gap-4"
            aria-label="HTTP check logs pagination"
            data-test="http-check-logs-pagination"
        >
            <Link
                v-if="pagination.current_page > 1"
                :href="previousPageHref"
                class="nb-button blue"
                data-test="http-check-logs-prev"
            >
                Previous
            </Link>
            <span
                v-else
                class="nb-button blue disabled"
                aria-disabled="true"
                data-test="http-check-logs-prev"
            >
                Previous
            </span>

            <p
                class="text-sm font-medium"
                data-test="http-check-logs-page-status"
            >
                Page {{ pagination.current_page }} of
                {{ pagination.last_page }}
            </p>

            <Link
                v-if="pagination.current_page < pagination.last_page"
                :href="nextPageHref"
                class="nb-button blue"
                data-test="http-check-logs-next"
            >
                Next
            </Link>
            <span
                v-else
                class="nb-button blue disabled"
                aria-disabled="true"
                data-test="http-check-logs-next"
            >
                Next
            </span>
        </nav>
    </div>
</template>

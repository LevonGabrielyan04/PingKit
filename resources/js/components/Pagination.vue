<script setup lang="ts">
import type { LinkComponentBaseProps } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type PageNumberItem =
    | { type: 'page'; page: number }
    | { type: 'ellipsis'; id: string };

type Props = {
    currentPage: number;
    lastPage: number;
    pageHref: (page: number) => LinkComponentBaseProps['href'];
    ariaLabel?: string;
    testId?: string;
};

const props = withDefaults(defineProps<Props>(), {
    ariaLabel: 'Pagination',
    testId: 'pagination',
});

const hasPages = computed(() => props.lastPage > 1);

/**
 * Sliding window of page numbers (Laravel UrlWindow style, onEachSide = 1):
 * - Show every page when lastPage < 10
 * - Near the start: 1..6 … last
 * - Near the end: 1 … (last-5)..last
 * - In the middle: 1 … (current-1)..(current+1) … last
 */
const pageNumberItems = computed((): PageNumberItem[] => {
    const current = props.currentPage;
    const last = props.lastPage;
    const onEachSide = 1;

    if (last <= 1) {
        return [];
    }

    if (last < onEachSide * 2 + 8) {
        return Array.from({ length: last }, (_, index) => ({
            type: 'page' as const,
            page: index + 1,
        }));
    }

    const window = onEachSide + 4;
    const items: PageNumberItem[] = [];

    const pushPages = (from: number, to: number): void => {
        for (let page = from; page <= to; page++) {
            items.push({ type: 'page', page });
        }
    };

    if (current <= window) {
        pushPages(1, window + onEachSide);
        items.push({ type: 'ellipsis', id: 'end' });
        items.push({ type: 'page', page: last });

        return items;
    }

    if (current > last - window) {
        items.push({ type: 'page', page: 1 });
        items.push({ type: 'ellipsis', id: 'start' });
        pushPages(last - window - onEachSide + 1, last);

        return items;
    }

    items.push({ type: 'page', page: 1 });
    items.push({ type: 'ellipsis', id: 'start' });
    pushPages(current - onEachSide, current + onEachSide);
    items.push({ type: 'ellipsis', id: 'end' });
    items.push({ type: 'page', page: last });

    return items;
});

const previousPageHref = computed(() => props.pageHref(props.currentPage - 1));

const nextPageHref = computed(() => props.pageHref(props.currentPage + 1));
</script>

<template>
    <nav
        v-if="hasPages"
        class="flex flex-wrap items-center justify-between gap-4"
        :aria-label="ariaLabel"
        :data-test="`${testId}-pagination`"
    >
        <Link
            v-if="currentPage > 1"
            :href="previousPageHref"
            class="nb-button blue"
            preserve-scroll
            :data-test="`${testId}-prev`"
        >
            Previous
        </Link>
        <span
            v-else
            class="nb-button blue disabled"
            aria-disabled="true"
            :data-test="`${testId}-prev`"
        >
            Previous
        </span>

        <div
            class="flex flex-wrap items-center justify-center gap-2"
            :data-test="`${testId}-page-numbers`"
        >
            <template
                v-for="item in pageNumberItems"
                :key="item.type === 'page' ? `page-${item.page}` : item.id"
            >
                <span
                    v-if="item.type === 'ellipsis'"
                    class="px-1 text-sm font-medium"
                    aria-hidden="true"
                    :data-test="`${testId}-page-ellipsis`"
                >
                    …
                </span>
                <span
                    v-else-if="item.page === currentPage"
                    class="nb-button blue disabled"
                    aria-current="page"
                    aria-disabled="true"
                    :data-test="`${testId}-page-${item.page}`"
                >
                    {{ item.page }}
                </span>
                <Link
                    v-else
                    :href="pageHref(item.page)"
                    class="nb-button blue"
                    preserve-scroll
                    :data-test="`${testId}-page-${item.page}`"
                >
                    {{ item.page }}
                </Link>
            </template>
        </div>

        <Link
            v-if="currentPage < lastPage"
            :href="nextPageHref"
            class="nb-button blue"
            preserve-scroll
            :data-test="`${testId}-next`"
        >
            Next
        </Link>
        <span
            v-else
            class="nb-button blue disabled"
            aria-disabled="true"
            :data-test="`${testId}-next`"
        >
            Next
        </span>
    </nav>
</template>

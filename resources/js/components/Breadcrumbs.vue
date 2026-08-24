<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

type Props = {
    breadcrumbs: BreadcrumbItemType[];
};

defineProps<Props>();
</script>

<template>
    <nav class="nb-breadcrumb !my-0" role="navigation" aria-label="Breadcrumb">
        <div
            v-for="(item, index) in breadcrumbs"
            :key="index"
            class="nb-breadcrumb-item"
        >
            <span
                v-if="index === breadcrumbs.length - 1"
                class="nb-breadcrumb-current"
                aria-current="page"
            >
                {{ item.title }}
            </span>
            <Link
                v-else
                :href="item.href"
                class="nb-breadcrumb-link"
            >
                {{ item.title }}
            </Link>
        </div>
    </nav>
</template>

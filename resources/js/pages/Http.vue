<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { http } from '@/routes';

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
</template>

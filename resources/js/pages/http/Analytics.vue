<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HttpNavbar from '@/components/HttpNavbar.vue';
import { analytics, errors } from '@/routes/http';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Http',
                href: errors(),
            },
            {
                title: 'Analytics',
                href: analytics(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Http Analytics" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <HttpNavbar />
    </div>
</template>

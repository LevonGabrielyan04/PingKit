<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PingTable from '@/components/PingTable.vue';
import { ping } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ping',
                href: ping(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Ping" />

    <PingTable />
</template>

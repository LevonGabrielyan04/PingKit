<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import MonitorForm from '@/components/MonitorForm.vue';
import { create, index as monitors } from '@/routes/monitors';

type HttpMethodOption = {
    value: number;
    label: string;
};

defineProps<{
    httpMethods: HttpMethodOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Monitors',
                href: monitors(),
            },
            {
                title: 'New Monitor',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="New Monitor" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <Heading
            title="New Monitor"
            description="Watch a URL or IP address with the HTTP method and headers you need."
        />

        <MonitorForm :http-methods="httpMethods" />
    </div>
</template>

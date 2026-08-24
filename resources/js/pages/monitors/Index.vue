<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import MonitorForm from '@/components/MonitorForm.vue';
import { index as monitors } from '@/routes/monitors';

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
        ],
    },
});
</script>

<template>
    <Head title="Monitors" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <Heading
            title="Monitors"
            description="Create and manage the endpoints you want to watch."
        />

        <MonitorForm :http-methods="httpMethods" />
    </div>
</template>

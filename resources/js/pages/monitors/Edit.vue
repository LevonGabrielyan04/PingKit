<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import MonitorForm from '@/components/MonitorForm.vue';
import { edit, index as monitors } from '@/routes/monitors';

type HttpMethodOption = {
    value: number;
    label: string;
};

type MonitorFormModel = {
    id: string;
    url_address: string | null;
    ip_address: string | null;
    request_method: number;
    request_headers: Record<string, string> | null;
    is_httpable: boolean;
};

const props = defineProps<{
    httpMethods: HttpMethodOption[];
    monitor: MonitorFormModel;
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Monitors',
            href: monitors(),
        },
        {
            title: 'Edit Monitor',
            href: edit(props.monitor.id),
        },
    ],
});
</script>

<template>
    <Head title="Edit Monitor" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <Heading
            title="Edit Monitor"
            description="Update the URL or IP address, HTTP method, and headers for this monitor."
        />

        <MonitorForm
            :http-methods="httpMethods"
            :monitor="monitor"
        />
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { create, edit, index as monitorsIndex } from '@/routes/monitors';

type MonitorListItem = {
    id: string;
    url_address: string | null;
    ip_address: string | null;
    request_method: string;
    is_httpable: boolean;
};

defineProps<{
    monitors: MonitorListItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Monitors',
                href: monitorsIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Monitors" />

    <div class="flex flex-col gap-6 px-4 py-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Monitors"
                description="Create and manage the endpoints you want to watch."
            />

            <Link
                :href="create()"
                class="nb-button orange"
                data-test="new-monitor"
            >
                New Monitor
            </Link>
        </div>

        <div
            v-if="monitors.length === 0"
            class="nb-card default"
            data-test="monitors-empty"
        >
            <div class="nb-card-content">
                <p class="nb-card-text">
                    No monitors yet. Create one to start watching an endpoint.
                </p>
            </div>
        </div>

        <div
            v-else
            class="nb-table-container orange overflow-x-auto"
            data-test="monitors-list"
        >
            <table class="nb-table orange bordered">
                <thead>
                    <tr>
                        <th scope="col">Target</th>
                        <th scope="col">Method</th>
                        <th scope="col">HTTP-able</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="monitor in monitors"
                        :key="monitor.id"
                        data-test="monitor-item"
                    >
                        <td>{{ monitor.url_address ?? monitor.ip_address }}</td>
                        <td>{{ monitor.request_method }}</td>
                        <td>{{ monitor.is_httpable ? 'Yes' : 'No' }}</td>
                        <td>
                            <Link
                                :href="edit(monitor.id)"
                                class="font-medium text-black underline underline-offset-2"
                                data-test="edit-monitor"
                            >
                                Edit
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

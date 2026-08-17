<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
        <form
            class="max-w-xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            data-test="dummy-ping-form"
            @submit.prevent
        >
            <Heading
                title="New ping"
                description="Dummy form for UI preview. Nothing is saved."
            />

            <div class="nb-form-group">
                <label for="ping-name">Name</label>
                <input
                    id="ping-name"
                    class="nb-input default"
                    type="text"
                    name="name"
                    placeholder="Homepage"
                />
            </div>

            <div class="nb-form-group">
                <label for="ping-url">URL</label>
                <input
                    id="ping-url"
                    class="nb-input default"
                    type="url"
                    name="url"
                    placeholder="https://example.com"
                />
            </div>

            <div class="nb-form-group">
                <label for="ping-method">Method</label>
                <select id="ping-method" class="nb-dropdown" name="method">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="HEAD">HEAD</option>
                </select>
            </div>

            <div class="nb-form-group">
                <label for="ping-notes">Notes</label>
                <textarea
                    id="ping-notes"
                    class="nb-textarea default"
                    name="notes"
                    placeholder="Optional notes"
                />
            </div>

            <div class="nb-form-group">
                <label class="nb-label" for="ping-enabled">
                    <input
                        id="ping-enabled"
                        class="nb-checkbox default"
                        type="checkbox"
                        name="enabled"
                        checked
                    />
                    Enabled
                </label>
            </div>

            <button type="submit" class="nb-button orange">Save ping</button>
        </form>
    </div>
</template>

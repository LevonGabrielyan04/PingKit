<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { analytics, errors } from '@/routes/http';

const { isCurrentUrl } = useCurrentUrl();

const title = computed(() => (isCurrentUrl(errors()) ? 'Errors' : 'Analytics'));

const description = computed(() =>
    isCurrentUrl(errors())
        ? 'Failed HTTP checks for your monitors.'
        : 'Insights and trends for your HTTP monitors.',
);
</script>

<template>
    <nav class="nb-navbar blue flex-wrap gap-4" aria-label="Http" data-test="http-navbar">
        <div class="min-w-0" data-test="http-nav-explainer">
            <p class="nb-navbar-brand">{{ title }}</p>
            <p class="mt-1 text-sm font-semibold normal-case tracking-normal text-black/70">
                {{ description }}
            </p>
        </div>

        <ul class="nb-navbar-nav ml-auto shrink-0">
            <li class="nb-navbar-item">
                <Link
                    :href="errors()"
                    class="nb-button"
                    :class="isCurrentUrl(errors()) ? 'blue' : 'default'"
                    data-test="http-nav-errors"
                >
                    Errors
                </Link>
            </li>
            <li class="nb-navbar-item">
                <Link
                    :href="analytics()"
                    class="nb-button"
                    :class="isCurrentUrl(analytics()) ? 'blue' : 'default'"
                    data-test="http-nav-analytics"
                >
                    Analytics
                </Link>
            </li>
        </ul>
    </nav>
</template>

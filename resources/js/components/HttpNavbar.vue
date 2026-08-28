<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { errors } from '@/routes/http';

const title = 'Errors';
const description = 'Failed HTTP checks for your monitors.';
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
                    class="nb-button blue"
                    data-test="http-nav-errors"
                >
                    Errors
                </Link>
            </li>
            <li class="nb-navbar-item">
                <a
                    :href="errors.export.url()"
                    class="nb-button default"
                    data-test="http-nav-download-excel"
                >
                    Download Excel
                </a>
            </li>
        </ul>
    </nav>
</template>

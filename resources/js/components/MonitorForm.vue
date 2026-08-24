<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { store } from '@/routes/monitors';

type HttpMethodOption = {
    value: number;
    label: string;
};

type Props = {
    httpMethods: HttpMethodOption[];
};

defineProps<Props>();

const targetType = ref<'url' | 'ip'>('url');

function transform(data: Record<string, unknown>) {
    const { target_type: _targetType, ...payload } = data;

    return {
        ...payload,
        is_httpable: Boolean(data.is_httpable),
    };
}
</script>

<template>
    <div
        class="nb-card default w-full max-w-none"
        data-test="monitor-form"
    >
        <div class="nb-card-content">
            <h4 class="nb-card-title">New monitor</h4>
            <p class="nb-card-text">
                Watch a URL or IP address with the HTTP method and headers you
                need.
            </p>

            <Form
                v-bind="store.form()"
                :transform="transform"
                reset-on-success
                v-slot="{ errors, processing }"
                class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2"
            >
                <div class="nb-form-group mb-0">
                    <label>Target</label>
                    <div class="flex flex-wrap gap-2">
                        <input
                            id="monitor-target-url"
                            v-model="targetType"
                            type="radio"
                            class="nb-radio"
                            name="target_type"
                            value="url"
                        />
                        <label for="monitor-target-url">URL</label>

                        <input
                            id="monitor-target-ip"
                            v-model="targetType"
                            type="radio"
                            class="nb-radio"
                            name="target_type"
                            value="ip"
                        />
                        <label for="monitor-target-ip">IP address</label>
                    </div>
                </div>

                <div class="nb-form-group mb-0">
                    <label for="monitor-request-method">Request method</label>
                    <select
                        id="monitor-request-method"
                        class="nb-dropdown"
                        name="request_method"
                        data-test="monitor-request-method"
                    >
                        <option
                            v-for="method in httpMethods"
                            :key="method.value"
                            :value="method.value"
                        >
                            {{ method.label }}
                        </option>
                    </select>
                    <InputError :message="errors.request_method" />
                </div>

                <div
                    v-if="targetType === 'url'"
                    class="nb-form-group mb-0"
                >
                    <label for="monitor-url-address">URL address</label>
                    <input
                        id="monitor-url-address"
                        type="url"
                        class="nb-input orange"
                        name="url_address"
                        placeholder="https://example.com"
                        autocomplete="off"
                        data-test="monitor-url-address"
                    />
                    <InputError :message="errors.url_address" />
                </div>

                <div
                    v-else
                    class="nb-form-group mb-0"
                >
                    <label for="monitor-ip-address">IP address</label>
                    <input
                        id="monitor-ip-address"
                        type="text"
                        class="nb-input orange"
                        name="ip_address"
                        placeholder="192.0.2.1"
                        autocomplete="off"
                        data-test="monitor-ip-address"
                    />
                    <InputError :message="errors.ip_address" />
                </div>

                <div class="nb-form-group mb-0">
                    <label class="nb-label">
                        <input
                            type="checkbox"
                            class="nb-checkbox orange"
                            name="is_httpable"
                            value="1"
                            checked
                            data-test="monitor-is-httpable"
                        />
                        HTTP-able
                    </label>
                    <InputError :message="errors.is_httpable" />
                </div>

                <div class="nb-form-group mb-0 md:col-span-2">
                    <label for="monitor-request-headers">Request headers</label>
                    <textarea
                        id="monitor-request-headers"
                        class="nb-textarea orange min-h-24"
                        name="request_headers"
                        placeholder='{"User-Agent":"PingKit"}'
                        data-test="monitor-request-headers"
                    />
                    <InputError :message="errors.request_headers" />
                </div>

                <div class="nb-card-actions md:col-span-2">
                    <button
                        type="submit"
                        class="nb-button orange"
                        :disabled="processing"
                        data-test="monitor-submit"
                    >
                        Create monitor
                    </button>
                </div>
            </Form>
        </div>
    </div>
</template>

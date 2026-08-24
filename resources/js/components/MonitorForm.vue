<script setup lang="ts">
import { ref } from 'vue';

type HttpMethodOption = {
    value: number;
    label: string;
};

type Props = {
    httpMethods: HttpMethodOption[];
};

const props = defineProps<Props>();

const targetType = ref<'url' | 'ip'>('url');
const urlAddress = ref('');
const ipAddress = ref('');
const requestMethod = ref(props.httpMethods[0]?.value ?? 1);
const requestHeaders = ref('');
const isHttpable = ref(true);
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

            <form
                class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2"
                @submit.prevent
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
                        v-model="requestMethod"
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
                </div>

                <div
                    v-if="targetType === 'url'"
                    class="nb-form-group mb-0"
                >
                    <label for="monitor-url-address">URL address</label>
                    <input
                        id="monitor-url-address"
                        v-model="urlAddress"
                        type="url"
                        class="nb-input orange"
                        name="url_address"
                        placeholder="https://example.com"
                        autocomplete="off"
                        data-test="monitor-url-address"
                    />
                </div>

                <div
                    v-else
                    class="nb-form-group mb-0"
                >
                    <label for="monitor-ip-address">IP address</label>
                    <input
                        id="monitor-ip-address"
                        v-model="ipAddress"
                        type="text"
                        class="nb-input orange"
                        name="ip_address"
                        placeholder="192.0.2.1"
                        autocomplete="off"
                        data-test="monitor-ip-address"
                    />
                </div>

                <div class="nb-form-group mb-0">
                    <label class="nb-label">
                        <input
                            v-model="isHttpable"
                            type="checkbox"
                            class="nb-checkbox orange"
                            name="is_httpable"
                            data-test="monitor-is-httpable"
                        />
                        HTTP-able
                    </label>
                </div>

                <div class="nb-form-group mb-0 md:col-span-2">
                    <label for="monitor-request-headers">Request headers</label>
                    <textarea
                        id="monitor-request-headers"
                        v-model="requestHeaders"
                        class="nb-textarea orange min-h-24"
                        name="request_headers"
                        placeholder='{"User-Agent":"PingKit"}'
                        data-test="monitor-request-headers"
                    />
                </div>

                <div class="nb-card-actions md:col-span-2">
                    <button
                        type="submit"
                        class="nb-button orange"
                        data-test="monitor-submit"
                    >
                        Create monitor
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

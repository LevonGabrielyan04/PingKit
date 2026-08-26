<?php

test('the http page includes the http check logs table', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/Http.vue');

    expect($page)
        ->toContain('Head title="Http"')
        ->toContain('HttpCheckLogsTable')
        ->toContain(':logs="logs"');
});

test('the http check logs table reflects http_check_logs fields', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/HttpCheckLogsTable.vue');

    expect($component)
        ->toContain('data-test="http-check-logs-empty"')
        ->toContain('data-test="http-check-logs-list"')
        ->toContain('data-test="http-check-log-item"')
        ->toContain('nb-table orange bordered')
        ->toContain('monitor_id')
        ->toContain('status_code')
        ->toContain('is_successful')
        ->toContain('response_time_ms')
        ->toContain('dns_time_ms')
        ->toContain('tcp_time_ms')
        ->toContain('tls_time_ms')
        ->toContain('error_message')
        ->toContain('created_at');
});

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
        ->toContain('>Target</')
        ->toContain('log.target')
        ->toContain('status_code')
        ->toContain('response_time_ms')
        ->toContain('dns_time_ms')
        ->toContain('tcp_time_ms')
        ->toContain('tls_time_ms')
        ->toContain('error_message')
        ->toContain('created_at')
        ->not->toContain('monitor_id')
        ->not->toContain('is_successful')
        ->not->toContain('>Result</')
        ->not->toContain('>Monitor</');
});

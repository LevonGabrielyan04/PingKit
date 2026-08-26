<?php

test('the http errors page includes the http check logs table', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/http/Errors.vue');

    expect($page)
        ->toContain('Head title="Http Errors"')
        ->toContain('HttpNavbar')
        ->toContain('HttpCheckLogsTable')
        ->toContain(':logs="logs"')
        ->toContain('Pagination')
        ->toContain(':current-page="pagination.current_page"')
        ->toContain(':last-page="pagination.last_page"')
        ->toContain(':page-href="pageHref"')
        ->toContain('test-id="http-check-logs"')
        ->toContain('usePageHref')
        ->toContain('usePageHref(errors)')
        ->not->toContain('data-test="http-check-logs-page-status"');
});

test('the http analytics page is empty besides the navbar', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/http/Analytics.vue');

    expect($page)
        ->toContain('Head title="Http Analytics"')
        ->toContain('HttpNavbar')
        ->not->toContain('HttpCheckLogsTable')
        ->not->toContain('Pagination');
});

test('the page href composable builds page one without a query string', function () {
    $composable = file_get_contents(dirname(__DIR__, 2).'/resources/js/composables/usePageHref.ts');

    expect($composable)
        ->toContain('export function usePageHref')
        ->toContain('page <= 1')
        ->toContain('route()')
        ->toContain('route({ query: { page } })');
});

test('the pagination component renders previous next and numbered links', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/Pagination.vue');

    expect($component)
        ->toContain('pageNumberItems')
        ->toContain('aria-current="page"')
        ->toContain('Previous')
        ->toContain('Next')
        ->toContain('`${testId}-pagination`')
        ->toContain('`${testId}-prev`')
        ->toContain('`${testId}-next`')
        ->toContain('`${testId}-page-numbers`')
        ->toContain('`${testId}-page-ellipsis`')
        ->toContain('`${testId}-page-${item.page}`')
        ->toContain('preserve-scroll')
        ->toContain('nb-button blue');
});

test('the http check logs table reflects http_check_logs fields', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/HttpCheckLogsTable.vue');

    expect($component)
        ->toContain('data-test="http-check-logs-empty"')
        ->toContain('data-test="http-check-logs-list"')
        ->toContain('data-test="http-check-log-item"')
        ->toContain('nb-table blue bordered')
        ->toContain('>Target</')
        ->toContain('log.target')
        ->toContain('status_code')
        ->toContain('response_time_ms')
        ->toContain('dns_time_ms')
        ->toContain('tcp_time_ms')
        ->toContain('tls_time_ms')
        ->toContain('error_message')
        ->toContain('response_headers')
        ->toContain('created_at')
        ->toContain('>Headers</')
        ->toContain('nb-dialog blue')
        ->toContain('nb-dialog-header')
        ->toContain('nb-dialog-body')
        ->toContain('overflow-y-auto')
        ->toContain('nb-dialog-footer')
        ->toContain('data-test="http-check-log-error-button"')
        ->toContain('font-medium text-black underline underline-offset-2')
        ->toContain('data-test="http-check-log-error-dialog"')
        ->toContain('data-test="http-check-log-error-hide"')
        ->toContain('>Total</')
        ->toContain('>Headers</')
        ->toContain('Hide')
        ->toContain('Response')
        ->not->toContain('Error Message')
        ->not->toContain('monitor_id')
        ->not->toContain('is_successful')
        ->not->toContain('>Result</')
        ->not->toContain('>Monitor</');
});

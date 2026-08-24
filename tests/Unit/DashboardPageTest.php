<?php

test('the dashboard page does not include dummy starter content', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/Dashboard.vue');

    expect($page)
        ->not->toContain('PlaceholderPattern')
        ->not->toContain('data-test="dummy-ping-form"')
        ->not->toContain('Dummy form for UI preview')
        ->not->toContain('PingTable')
        ->toContain('Head title="Dashboard"');
});

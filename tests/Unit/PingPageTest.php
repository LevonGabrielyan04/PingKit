<?php

test('the ping page includes the ping table', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/Ping.vue');

    expect($page)
        ->toContain('Head title="Ping"')
        ->toContain('PingTable');
});

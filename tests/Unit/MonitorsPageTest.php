<?php

test('the monitors page includes the monitor form', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/monitors/Index.vue');

    expect($page)
        ->toContain('Head title="Monitors"')
        ->toContain('MonitorForm')
        ->toContain("title: 'Monitors'");
});

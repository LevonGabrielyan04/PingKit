<?php

test('the monitors page lists monitors and links to create', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/monitors/Index.vue');

    expect($page)
        ->toContain('Head title="Monitors"')
        ->toContain("title: 'Monitors'")
        ->toContain('data-test="new-monitor"')
        ->toContain('New Monitor')
        ->toContain("import { create, index as monitorsIndex } from '@/routes/monitors'")
        ->toContain(':href="create()"')
        ->toContain('data-test="monitors-list"')
        ->toContain('data-test="monitor-item"')
        ->toContain('nb-table')
        ->toContain('<table')
        ->not->toContain('MonitorForm');
});

test('the monitor creation page includes the monitor form', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/monitors/Create.vue');

    expect($page)
        ->toContain('Head title="New Monitor"')
        ->toContain('MonitorForm')
        ->toContain("title: 'New Monitor'")
        ->toContain("import { create, index as monitors } from '@/routes/monitors'");
});

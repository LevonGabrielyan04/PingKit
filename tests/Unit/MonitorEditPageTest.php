<?php

test('the monitors page lists monitors with an edit button per row', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/monitors/Index.vue');

    expect($page)
        ->toContain('Head title="Monitors"')
        ->toContain("title: 'Monitors'")
        ->toContain('data-test="new-monitor"')
        ->toContain('New Monitor')
        ->toContain("import { create, edit, index as monitorsIndex } from '@/routes/monitors'")
        ->toContain(':href="create()"')
        ->toContain(':href="edit(monitor.id)"')
        ->toContain('data-test="edit-monitor"')
        ->toContain('Edit')
        ->toContain('data-test="monitors-list"')
        ->toContain('data-test="monitor-item"')
        ->toContain('nb-table')
        ->toContain('<table')
        ->toContain('Actions')
        ->not->toContain('MonitorForm');
});

test('the monitor edit page includes the monitor form', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/monitors/Edit.vue');

    expect($page)
        ->toContain('Head title="Edit Monitor"')
        ->toContain('MonitorForm')
        ->toContain(':monitor="monitor"')
        ->toContain("title: 'Edit Monitor'")
        ->toContain('setLayoutProps')
        ->toContain("import { edit, index as monitors } from '@/routes/monitors'");
});

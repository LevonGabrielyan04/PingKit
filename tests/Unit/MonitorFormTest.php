<?php

test('the monitor form uses NeoBrutalismCSS field classes', function () {
    $form = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/MonitorForm.vue');

    expect($form)
        ->toContain('data-test="monitor-form"')
        ->toContain('nb-card')
        ->toContain('nb-form-group')
        ->toContain('nb-input')
        ->toContain('nb-dropdown')
        ->toContain('nb-textarea')
        ->toContain('nb-checkbox')
        ->toContain('nb-button')
        ->toContain('nb-radio')
        ->toContain('name="url_address"')
        ->toContain('name="ip_address"')
        ->toContain('name="request_method"')
        ->toContain('name="request_headers"')
        ->toContain('name="is_httpable"')
        ->toContain('grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2')
        ->toContain('max-w-none');
});

test('nb-form-group radio option labels keep inline-flex alignment', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    expect($css)
        ->toContain('.nb-form-group .nb-radio + label')
        ->toContain('display: inline-flex')
        ->toContain('align-items: center')
        ->toContain('margin-bottom: 0');
});

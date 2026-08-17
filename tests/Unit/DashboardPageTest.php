<?php

test('the dashboard page includes a dummy form with NeoBrutalismCSS classes', function () {
    $page = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/Dashboard.vue');

    expect($page)
        ->toContain('data-test="dummy-ping-form"')
        ->toContain('class="nb-input default"')
        ->toContain('class="nb-dropdown"')
        ->toContain('class="nb-textarea default"')
        ->toContain('class="nb-checkbox default"')
        ->toContain('class="nb-button default"')
        ->toContain('@submit.prevent');
});

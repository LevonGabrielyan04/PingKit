<?php

test('the http navbar uses neobrutalism css with errors and download excel buttons', function () {
    $navbar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/HttpNavbar.vue');

    expect($navbar)
        ->toContain('nb-navbar blue')
        ->toContain('nb-navbar-brand')
        ->toContain('nb-navbar-nav')
        ->toContain('ml-auto')
        ->toContain('nb-button')
        ->toContain('Errors')
        ->toContain('Download Excel')
        ->toContain('data-test="http-navbar"')
        ->toContain('data-test="http-nav-explainer"')
        ->toContain('data-test="http-nav-errors"')
        ->toContain('data-test="http-nav-download-excel"')
        ->toContain("from '@/routes/http'")
        ->toContain('errors()')
        ->toContain('errors.export.url()')
        ->not->toContain('Analytics')
        ->not->toContain('analytics()')
        ->not->toContain("from '@/routes'");
});

<?php

test('the http navbar uses neobrutalism css with errors and analytics buttons', function () {
    $navbar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/HttpNavbar.vue');

    expect($navbar)
        ->toContain('nb-navbar blue')
        ->toContain('nb-navbar-brand')
        ->toContain('nb-navbar-nav')
        ->toContain('ml-auto')
        ->toContain('nb-button')
        ->toContain('Errors')
        ->toContain('Analytics')
        ->toContain('data-test="http-navbar"')
        ->toContain('data-test="http-nav-explainer"')
        ->toContain('data-test="http-nav-errors"')
        ->toContain('data-test="http-nav-analytics"')
        ->toContain("from '@/routes/http'")
        ->toContain('errors()')
        ->toContain('analytics()')
        ->toContain('useCurrentUrl')
        ->not->toContain("from '@/routes'");
});

<?php

test('the sidebar includes ping and http pages', function () {
    $sidebar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/AppSidebar.vue');

    expect($sidebar)
        ->not->toContain('https://github.com/laravel/vue-starter-kit')
        ->not->toContain('https://laravel.com/docs/starter-kits#vue')
        ->not->toContain('NavFooter')
        ->toContain("title: 'Dashboard'")
        ->toContain("title: 'Ping'")
        ->toContain("title: 'Http'");
});

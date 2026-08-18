<?php

test('the sidebar does not include starter kit footer links', function () {
    $sidebar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/AppSidebar.vue');

    expect($sidebar)
        ->not->toContain('https://github.com/laravel/vue-starter-kit')
        ->not->toContain('https://laravel.com/docs/starter-kits#vue')
        ->not->toContain('NavFooter')
        ->toContain("title: 'Dashboard'");
});

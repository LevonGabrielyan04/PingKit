<?php

test('the sidebar includes ping, http, and monitors pages', function () {
    $sidebar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/AppSidebar.vue');

    expect($sidebar)
        ->not->toContain('https://github.com/laravel/vue-starter-kit')
        ->not->toContain('https://laravel.com/docs/starter-kits#vue')
        ->not->toContain('NavFooter')
        ->toContain("title: 'Dashboard'")
        ->toContain("title: 'Ping'")
        ->toContain("title: 'Http'")
        ->toContain("title: 'Monitors'");
});

test('the sidebar uses NeoBrutalismCSS styling to match monitors', function () {
    $sidebar = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/AppSidebar.vue');
    $navMain = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/NavMain.vue');
    $layout = file_get_contents(dirname(__DIR__, 2).'/resources/js/layouts/app/AppSidebarLayout.vue');
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    expect($sidebar)
        ->toContain('nb-app-sidebar')
        ->toContain('nb-navbar-brand');

    expect($navMain)->toContain('nb-sidebar-link');

    expect($layout)->toContain('nb-app-inset');

    expect($css)
        ->toContain('.nb-app-sidebar')
        ->toContain('.nb-sidebar-link')
        ->toContain('#ff5733');
});

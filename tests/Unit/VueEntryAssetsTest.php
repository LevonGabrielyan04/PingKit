<?php

test('the app stylesheet imports NeoBrutalismCSS globally', function () {
    $stylesheet = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
    $entry = file_get_contents(dirname(__DIR__, 2).'/resources/js/app.ts');

    expect($stylesheet)->toContain('@import "neobrutalismcss"')
        ->and($entry)->not->toContain("import 'neobrutalismcss'");
});

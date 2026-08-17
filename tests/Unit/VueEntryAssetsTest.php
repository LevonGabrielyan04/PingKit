<?php

test('the vue entry file imports NeoBrutalismCSS globally', function () {
    $entry = file_get_contents(dirname(__DIR__, 2).'/resources/js/app.ts');

    expect($entry)->toContain("import 'neobrutalismcss'");
});

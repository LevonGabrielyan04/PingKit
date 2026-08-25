<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Max Request Chunk Size
    |--------------------------------------------------------------------------
    |
    | Upper bound for how many httpable monitors may be loaded per chunk when
    | building concurrent outbound requests.
    |
    */

    'max_chunk_size' => (int) env('MONITORS_MAX_CHUNK_SIZE', 200),

];

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

    /*
    |--------------------------------------------------------------------------
    | HTTP Check Pool
    |--------------------------------------------------------------------------
    |
    | Concurrency and timeouts for the Guzzle Pool that executes outbound
    | monitor checks.
    |
    */

    'concurrency' => (int) env('MONITORS_CONCURRENCY', 25),

    'timeout' => (float) env('MONITORS_TIMEOUT', 10),

    'connect_timeout' => (float) env('MONITORS_CONNECT_TIMEOUT', 5),

];

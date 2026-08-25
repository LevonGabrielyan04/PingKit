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

    /*
    |--------------------------------------------------------------------------
    | Poll Queue
    |--------------------------------------------------------------------------
    |
    | Cursor-paged HTTP check jobs are dispatched to this queue. Workers should
    | listen on it (e.g. queue:work --queue=Polls). poll_chunk_size is both the
    | fan-out page size and executePage() limit; keep it <= max_chunk_size.
    |
    */

    'polls_queue' => env('MONITORS_POLLS_QUEUE', 'Polls'),

    'poll_chunk_size' => (int) env('MONITORS_POLL_CHUNK_SIZE', 100),

];

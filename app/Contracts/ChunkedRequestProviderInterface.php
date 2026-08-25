<?php

namespace App\Contracts;

use Generator;
use GuzzleHttp\Psr7\Request;

interface ChunkedRequestProviderInterface
{
    /**
     * Yield Guzzle Pool–ready requests for httpable monitors, loaded in chunks.
     *
     * @return Generator<string, Request>
     */
    public function requests(int $chunkSize = 100): Generator;

    /**
     * Yield Guzzle Pool–ready requests for a single httpable monitor page.
     *
     * @return Generator<string, Request>
     */
    public function requestsPage(?string $afterId, int $limit): Generator;
}

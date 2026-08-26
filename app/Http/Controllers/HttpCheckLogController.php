<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckLogData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HttpCheckLogController extends Controller
{
    public function __construct(private HttpCheckLogRepositoryInterface $httpCheckLogs) {}

    /**
     * Display failed HTTP check logs.
     */
    public function index(Request $request): Response
    {
        $logs = $this->httpCheckLogs->paginateFailed($request->user());

        return Inertia::render('Http', [
            'logs' => array_map(
                fn (HttpCheckLogData $log): array => $log->toArray(),
                $logs->items(),
            ),
        ]);
    }
}

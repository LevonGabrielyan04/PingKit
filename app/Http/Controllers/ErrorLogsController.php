<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckLogData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ErrorLogsController extends Controller
{
    public function __construct(private HttpCheckLogRepositoryInterface $httpCheckLogs) {}

    /**
     * Display failed HTTP check logs.
     */
    public function index(Request $request): Response
    {
        $logs = $this->httpCheckLogs->paginateFailed($request->user());

        return Inertia::render('http/Errors', [
            'logs' => array_map(
                fn (HttpCheckLogData $log): array => $log->toArray(),
                $logs->items(),
            ),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}

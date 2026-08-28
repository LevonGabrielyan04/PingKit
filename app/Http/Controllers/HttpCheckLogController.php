<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckLogData;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HttpCheckLogController extends Controller
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

    /**
     * Download failed HTTP check logs as a CSV file.
     */
    public function export(Request $request, CsvExportService $csv): BinaryFileResponse
    {
        $path = $csv->export(
            config('http.errors_export_columns'),
            $this->httpCheckLogs->exportFailedQuery($request->user()),
        );

        return response()
            ->download($path, 'http-errors.csv', ['Content-Type' => 'text/csv'])
            ->deleteFileAfterSend();
    }
}

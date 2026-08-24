<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\MonitorRepositoryInterface;
use App\Http\Requests\StoreMonitorRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class MonitorController extends Controller
{
    public function __construct(private MonitorRepositoryInterface $monitors) {}

    /**
     * Display the monitors page.
     */
    public function index(): Response
    {
        return $this->monitors->index();
    }

    /**
     * Store a newly created monitor.
     */
    public function store(StoreMonitorRequest $request): RedirectResponse
    {
        $this->monitors->store($request->user(), $request->validated());

        return to_route('monitors.index');
    }
}

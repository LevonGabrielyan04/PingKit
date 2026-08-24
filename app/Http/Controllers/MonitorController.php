<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\MonitorRepositoryInterface;
use App\Http\Requests\StoreMonitorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class MonitorController extends Controller
{
    public function __construct(private MonitorRepositoryInterface $monitors) {}

    /**
     * Display the monitors page.
     */
    public function index(Request $request): Response
    {
        return $this->monitors->index($request->user());
    }

    /**
     * Show the form for creating a new monitor.
     */
    public function create(): Response
    {
        return $this->monitors->create();
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

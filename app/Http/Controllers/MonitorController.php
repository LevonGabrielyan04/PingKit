<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\MonitorRepositoryInterface;
use App\Http\Requests\StoreMonitorRequest;
use App\Http\Requests\UpdateMonitorRequest;
use App\Models\Monitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Response;

class MonitorController extends Controller implements HasMiddleware
{
    public function __construct(private MonitorRepositoryInterface $monitors) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Monitor::class, only: ['index']),
            new Middleware('can:view,monitor', only: ['edit']),
            new Middleware('can:delete,monitor', only: ['destroy']),
        ];
    }

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

    /**
     * Show the form for editing the specified monitor.
     */
    public function edit(Monitor $monitor): Response
    {
        return $this->monitors->edit($monitor);
    }

    /**
     * Update the specified monitor.
     */
    public function update(UpdateMonitorRequest $request, Monitor $monitor): RedirectResponse
    {
        $this->monitors->update($monitor, $request->validated());

        return to_route('monitors.index');
    }

    /**
     * Remove the specified monitor.
     */
    public function destroy(Monitor $monitor): RedirectResponse
    {
        $this->monitors->delete($monitor);

        return to_route('monitors.index');
    }
}

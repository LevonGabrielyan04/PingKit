<?php

namespace App\Http\Controllers;

use App\Enums\HttpMethod;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller
{
    /**
     * Display the monitors page.
     */
    public function index(): Response
    {
        return Inertia::render('monitors/Index', [
            'httpMethods' => collect(HttpMethod::cases())
                ->map(fn (HttpMethod $method): array => [
                    'value' => $method->value,
                    'label' => $method->label(),
                ])
                ->values()
                ->all(),
        ]);
    }
}

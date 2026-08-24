<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Monitor;

class UpdateMonitorRequest extends MonitorRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Monitor $monitor */
        $monitor = $this->route('monitor');

        return $this->user()->can('update', $monitor);
    }
}

<?php

namespace App\Models;

use Database\Factories\HttpCheckLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $monitor_id
 * @property Carbon $created_at
 * @property int $status_code
 * @property bool $is_successful
 * @property int $response_time_ms
 * @property int|null $dns_time_ms
 * @property int|null $tcp_time_ms
 * @property int|null $tls_time_ms
 * @property string|null $error_message
 * @property array<string, mixed>|null $response_headers
 * @property array<string, mixed>|null $request_headers
 */
#[Fillable([
    'monitor_id',
    'status_code',
    'response_time_ms',
    'dns_time_ms',
    'tcp_time_ms',
    'tls_time_ms',
    'error_message',
    'response_headers',
    'request_headers',
])]
class HttpCheckLog extends Model
{
    /** @use HasFactory<HttpCheckLogFactory> */
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_successful' => 'boolean',
            'response_headers' => 'array',
            'request_headers' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Monitor, $this>
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }
}

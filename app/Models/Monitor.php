<?php

namespace App\Models;

use App\Enums\HttpMethod;
use Carbon\CarbonImmutable;
use Database\Factories\MonitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int $user_id
 * @property string|null $url_address
 * @property string|null $ip_address
 * @property HttpMethod $request_method
 * @property array<string, mixed>|null $request_headers
 * @property bool $is_httpable
 * @property CarbonImmutable|null $checked_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['user_id', 'url_address', 'ip_address', 'request_method', 'request_headers', 'is_httpable', 'checked_at'])]
class Monitor extends Model
{
    /** @use HasFactory<MonitorFactory> */
    use HasFactory, HasUuids;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'request_method' => HttpMethod::Get->value,
        'is_httpable' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_method' => HttpMethod::class,
            'request_headers' => 'array',
            'is_httpable' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<HttpCheckLog, $this>
     */
    public function httpCheckLogs(): HasMany
    {
        return $this->hasMany(HttpCheckLog::class);
    }
}

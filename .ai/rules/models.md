---
paths:
  - 'app/Models/*.php'
---

# Models

## Monitor and HttpCheckLog use HasUuids
Monitor and HttpCheckLog use UUID primary keys via HasUuids (UUIDv7). Do not set `id` in their factories. HttpCheckLog has `created_at` only (`UPDATED_AT = null`). `is_successful` is a stored generated column — never mass-assign it.

## Cast Monitor request_method to HttpMethod
Monitor.request_method is an int-backed App\Enums\HttpMethod. Keep it in Fillable, casts(), PHPDoc, and $attributes (raw value HttpMethod::Get->value). Factory should set HttpMethod::Get.

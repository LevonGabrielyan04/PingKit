---
paths:
  - 'app/Models/*.php'
---

# Models

## Monitor and HttpCheckLog use HasUuids
Monitor and HttpCheckLog use UUID primary keys via HasUuids (UUIDv7). Do not set `id` in their factories. HttpCheckLog has `created_at` only (`UPDATED_AT = null`). `is_successful` is a stored generated column — never mass-assign it.

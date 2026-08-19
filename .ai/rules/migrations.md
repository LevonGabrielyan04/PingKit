---
paths:
  - 'database/migrations/**'
---

# Migrations

## Monitor target is URL xor IP
The monitors table uses a UUID primary key (`uuid('id')->primary()`), a required `user_id` FK to users with cascadeOnDelete, and nullable `url_address` / `ip_address` columns. A CHECK constraint (`monitors_url_or_ip_address`) requires exactly one of url_address or ip_address to be null — never both set and never both null. Add matching CHECK constraints with DB::statement, same as http_check_logs.

## Monitor request_method is unsigned tinyint
monitors.request_method is an unsigned tinyint with default HttpMethod::Get (1). Do not use a native SQL ENUM or string column. Cast it on the Monitor model to App\Enums\HttpMethod.

---
paths:
  - 'database/migrations/**'
---

# Migrations

## Monitor target is URL xor IP
The monitors table uses a UUID primary key (`uuid('id')->primary()`), a required `user_id` FK to users with cascadeOnDelete, and nullable `url_address` / `ip_address` columns. A CHECK constraint (`monitors_url_or_ip_address`) requires exactly one of url_address or ip_address to be null — never both set and never both null. Add matching CHECK constraints with DB::statement, same as http_check_logs.

## UUID primary keys default to UUID_v7()
The monitors and http_check_logs id columns are UUID primary keys with a MariaDB default of UUID_v7() via `default(new Expression('(UUID_v7())'))`. Inserts may omit id; the database generates a time-ordered UUIDv7. Do not switch these keys to auto-increment integers or UUID v4 defaults.

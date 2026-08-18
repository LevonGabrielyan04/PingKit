---
paths:
  - 'database/migrations/**'
---

# Migrations

## Monitor target is URL xor IP
The monitors table uses a UUID primary key (`uuid('id')->primary()`), a required `user_id` FK to users with cascadeOnDelete, and nullable `url_address` / `ip_address` columns. A CHECK constraint (`monitors_url_or_ip_address`) requires exactly one of url_address or ip_address to be null — never both set and never both null. Add matching CHECK constraints with DB::statement, same as http_check_logs.

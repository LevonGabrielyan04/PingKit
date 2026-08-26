---
paths:
  - 'database/migrations/**'
---

# Migrations

## Monitor target is URL xor IP
The monitors table uses a UUID primary key (`uuid('id')->primary()`), a required `user_id` FK to users with cascadeOnDelete, and nullable `url_address` / `ip_address` columns. A CHECK constraint (`monitors_url_or_ip_address`) requires exactly one of url_address or ip_address to be null — never both set and never both null. Add matching CHECK constraints with DB::statement, same as http_check_logs.

## Monitor request_method is unsigned tinyint
monitors.request_method is an unsigned tinyint with default HttpMethod::Get (1). Do not use a native SQL ENUM or string column. Cast it on the Monitor model to App\Enums\HttpMethod.

## Monitor is_httpable boolean with index
monitors.is_httpable is a non-nullable boolean defaulting to true, with an index. Mirror the default in Monitor $attributes and cast it to boolean. Keep it in Fillable and the factory.

## Monitor checked_at is nullable indexed timestamp
monitors.checked_at is a nullable timestamp with an index. It tracks when a monitor was last checked; leave null until the first check. Cast it to datetime on the Monitor model; do not put it in Fillable.

## Monitor checked_at is nullable indexed timestamp
monitors.checked_at is a nullable timestamp with an index. It tracks when a monitor was last checked; leave null until the first check. Cast it to datetime on the Monitor model and keep it in Fillable.

## Composite indexes need a column array
`$table->index('col_a', 'col_b')` treats the second argument as the index *name*, not a second column — you get a single-column index named `col_b`. For a composite index use `$table->index(['col_a', 'col_b'])`.

## http_check_logs response_headers max length
http_check_logs.response_headers has CHECK http_check_logs_response_headers_max_length: null or CHAR_LENGTH(CAST(as char)) <= 5000. writeLogs nulls oversized encoded JSON before insert so inserts do not violate the constraint.

## http_check_logs error_message max length
http_check_logs.error_message is nullable text with CHECK http_check_logs_error_message_max_length: null or CHAR_LENGTH <= 3000. HttpCheckResult::fromFailure and writeLogs truncate with Str::limit(..., 3000, '') so inserts do not violate the constraint.

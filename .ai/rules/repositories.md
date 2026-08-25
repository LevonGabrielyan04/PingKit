---
paths:
  - 'app/Repositories/**/*.php'
---

# Repositories

## Monitor persistence goes through MonitorRepository
Create/update/delete Monitor persistence lives in App\Repositories\MonitorRepository. Controllers inject the repository and do not call monitors()->create() (or similar) directly.

## Httpable monitors via lazyHttpableById
Chunked outbound request building loads httpable monitors through MonitorRepositoryInterface::lazyHttpableById(), not Monitor::query() in services. Select only id, url_address, ip_address, request_method, request_headers and use lazyById for chunking.

## HttpCheckLog persistence via repository
Persist pool check results with HttpCheckLogRepositoryInterface::writeLogs($results). Binding is in AppServiceProvider. Never mass-assign is_successful; writeLogs inserts UUIDv7 ids and JSON-encodes headers.

## writeLogs accepts HttpCheckResult DTOs
HttpCheckLogRepositoryInterface::writeLogs() takes array<string, HttpCheckResult>, not associative arrays. Read camelCase DTO properties when inserting rows.

## Httpable page queries via afterId cursor
Single-page httpable loads use MonitorRepositoryInterface::httpableIdsAfterId() / httpablePageAfterId(?string $afterId, int $limit)—not unscoped lazyHttpableById() inside Polls chunk jobs. Select the same request columns as lazyHttpableById for pages.

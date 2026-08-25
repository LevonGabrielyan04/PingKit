---
paths:
  - 'app/Repositories/**/*.php'
---

# Repositories

## Monitor persistence goes through MonitorRepository
Create/update/delete Monitor persistence lives in App\Repositories\MonitorRepository. Controllers inject the repository and do not call monitors()->create() (or similar) directly.

## Httpable monitors via lazyHttpableById
Chunked outbound request building loads httpable monitors through MonitorRepositoryInterface::lazyHttpableById(), not Monitor::query() in services. Select only id, url_address, ip_address, request_method, request_headers and use lazyById for chunking.

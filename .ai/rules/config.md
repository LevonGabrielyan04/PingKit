---
paths:
  - config/cache.php
  - config/pulse.php
---

# Config

## Pulse cache serializable_classes allow-list
Laravel 13 defaults cache.serializable_classes to false. Pulse RemembersQueries caches Collection, stdClass, and CarbonImmutable for dashboard cards; without those three in the allow-list, /pulse Livewire polls fatal with incomplete object on Collection. Keep the list minimal; do not set true.

## Pulse Redis ingest connection
PULSE_INGEST_DRIVER=redis with PULSE_REDIS_CONNECTION=pulse (Redis DB 2 via database.redis.pulse). Keep it off the queue Redis connection (default/DB 0). Storage stays database. Run php artisan pulse:work or the dashboard stays empty.

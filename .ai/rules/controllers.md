---
paths:
  - app/Http/Controllers/MonitorController.php
---

# Controllers

## Inject MonitorRepositoryInterface, not concrete
Type-hint App\Contracts\MonitorRepositoryInterface in controllers, not the concrete MonitorRepository. Binding lives in AppServiceProvider::register().

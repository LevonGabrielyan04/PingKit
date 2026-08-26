---
paths:
  - app/Http/Controllers/MonitorController.php
  - app/Http/Controllers/HttpCheckLogController.php
---

# Controllers

## Inject MonitorRepositoryInterface, not concrete
Type-hint App\Contracts\MonitorRepositoryInterface in controllers, not the concrete MonitorRepository. Binding lives in AppServiceProvider::register().

## Http page served by HttpCheckLogController
GET /http is HttpCheckLogController@index (not Route::inertia). Inject HttpCheckLogRepositoryInterface, call paginateFailed($request->user()), pass logs as an array of HttpCheckLogData->toArray() to Inertia Http.

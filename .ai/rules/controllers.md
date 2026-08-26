---
paths:
  - app/Http/Controllers/MonitorController.php
  - app/Http/Controllers/HttpCheckLogController.php
---

# Controllers

## Inject MonitorRepositoryInterface, not concrete
Type-hint App\Contracts\MonitorRepositoryInterface in controllers, not the concrete MonitorRepository. Binding lives in AppServiceProvider::register().

## Http page served by HttpCheckLogController
GET /http/errors is HttpCheckLogController@index (not Route::inertia). GET /http redirects to /http/errors. Inject HttpCheckLogRepositoryInterface, call paginateFailed($request->user()), pass logs as an array of HttpCheckLogData->toArray() to Inertia http/Errors.

## Http page passes pagination meta
HttpCheckLogController@index still passes logs as HttpCheckLogData->toArray() items, plus a pagination prop: current_page, last_page, per_page, total from paginateFailed(). Do not drop pagination when changing the Http Inertia payload.

---
paths:
  - app/Http/Controllers/MonitorController.php
  - app/Http/Controllers/HttpCheckLogController.php
---

# Controllers

## Inject MonitorRepositoryInterface, not concrete
Type-hint App\Contracts\MonitorRepositoryInterface in controllers, not the concrete MonitorRepository. Binding lives in AppServiceProvider::register().

## Http page served by HttpCheckLogController
GET /http/errors is HttpCheckLogController@index (not Route::inertia). GET /http redirects to /http/errors. Inject HttpCheckLogRepositoryInterface, call paginateFailed($request->user()), pass logs as an array of HttpCheckLogData->toArray() to Inertia http/Errors. GET /http/errors/export is HttpCheckLogController@export for CSV download via CsvExportService.

## Http page passes pagination meta
HttpCheckLogController@index still passes logs as HttpCheckLogData->toArray() items, plus a pagination prop: current_page, last_page, per_page, total from paginateFailed(). Do not drop pagination when changing the Http Inertia payload.

## MonitorController auth via HasMiddleware
MonitorController implements HasMiddleware and maps can middleware to MonitorPolicy (viewAny→index, view→edit, delete→destroy). Do not call Gate::authorize in action methods. create/update stay on Form Requests.

## Http errors CSV export endpoint
GET /http/errors/export (http.errors.export) is HttpCheckLogController@export. It builds CSV via CsvExportService + HttpCheckLogRepositoryInterface::exportFailedQuery($user), returns response()->download(..., 'http-errors.csv')->deleteFileAfterSend(). Column headers mirror the Errors table (Checked at, Target, Status, timings, Error, Headers).

## HTTP errors export columns in config
Http errors CSV column map lives in config/http.php as errors_export_columns (header label => attribute path). HttpCheckLogController@export passes config('http.errors_export_columns') to CsvExportService; do not inline the array in the controller.

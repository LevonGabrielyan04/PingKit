---
paths:
  - 'app/Http/Requests/*Monitor*.php'
---

# Requests

## Monitor Form Requests authorize via MonitorPolicy
Monitor store/update Form Requests must call `$this->user()->can(...)` against MonitorPolicy (e.g. create → Monitor::class). Do not return true blindly in authorize(); keep authorization in the policy.

## Shared MonitorRequest parent for store/update
StoreMonitorRequest and UpdateMonitorRequest extend abstract MonitorRequest for shared rules() and prepareForValidation(). Keep authorize() on each child (create vs update via MonitorPolicy).

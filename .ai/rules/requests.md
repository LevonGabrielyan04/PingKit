---
paths:
  - 'app/Http/Requests/*Monitor*.php'
---

# Requests

## Monitor Form Requests authorize via MonitorPolicy
Monitor store/update Form Requests must call `$this->user()->can(...)` against MonitorPolicy (e.g. create → Monitor::class). Do not return true blindly in authorize(); keep authorization in the policy.

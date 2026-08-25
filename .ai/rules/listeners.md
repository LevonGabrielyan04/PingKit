---
paths:
  - 'app/Listeners/**/*.php'
---

# Listeners

## MarkMonitorsCheckedAt stamps checked_at
MarkMonitorsCheckedAt listens to HttpChecksCompleted, takes array_keys($event->results), and calls MonitorRepositoryInterface::markCheckedAt() to set monitors.checked_at to now(). Do not update checked_at inside HttpCheckPoolService.

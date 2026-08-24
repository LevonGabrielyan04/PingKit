---
paths:
  - 'app/Repositories/**/*.php'
---

# Repositories

## Monitor persistence goes through MonitorRepository
Create/update/delete Monitor persistence lives in App\Repositories\MonitorRepository. Controllers inject the repository and do not call monitors()->create() (or similar) directly.

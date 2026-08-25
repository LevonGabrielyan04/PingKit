---
paths:
  - 'tests/**/*.php'
---

# Tests

## Disable Vite in tests
Call withoutVite() from Tests\TestCase::setUp so Inertia page visits do not require a current public/build manifest. public/build is gitignored and goes stale when new Vue pages are added.

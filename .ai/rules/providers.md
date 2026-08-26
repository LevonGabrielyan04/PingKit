---
paths:
  - app/Providers/AppServiceProvider.php
---

# Providers

## Pulse viewPulse gate
Pulse dashboard uses Gate::define('viewPulse') in AppServiceProvider::configurePulse. Local allows anyone; non-local requires an authenticated user. There is no admin role. phpunit.xml sets PULSE_ENABLED=false so tests do not record Pulse data.

## Pulse viewPulse admin email
viewPulse allows only the authenticated user whose email matches config('app.admin_email') from ADMIN_EMAIL (case-insensitive). Empty ADMIN_EMAIL denies everyone. Do not use env() in the gate.

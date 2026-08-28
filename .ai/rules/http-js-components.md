---
paths:
  - 'resources/js/pages/http/**/*.vue'
  - resources/js/components/HttpNavbar.vue
---

# Http Js Components

## Http page has Errors/Download Excel nb-navbar
http/Errors.vue uses HttpNavbar (nb-navbar blue): left side has Errors title + short description; right side has Errors nb-button link (blue on /http/errors) and Download Excel plain anchor via Wayfinder errors.export.url() (CSV download, not Inertia). GET /http/errors is failed logs table; GET /http/errors/export downloads http-errors.csv. GET /http redirects to /http/errors. Keep explainer left, buttons right (nb-navbar-nav ml-auto).

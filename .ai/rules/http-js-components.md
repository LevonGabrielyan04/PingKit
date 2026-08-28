---
paths:
  - 'resources/js/pages/http/**/*.vue'
  - resources/js/components/HttpNavbar.vue
---

# Http Js Components

## Http page has Errors/Analytics nb-navbar
http/Errors.vue and http/Analytics.vue share HttpNavbar (nb-navbar blue): left side has a contextual nb-navbar-brand title + short description (Errors vs Analytics by route); right side has Errors + Analytics nb-button links (nb-navbar-nav ml-auto) via Wayfinder (errors(), analytics()). Errors is GET /http/errors (failed logs table); Analytics is GET /http/analytics (empty for now). GET /http redirects to /http/errors. Active button uses blue, inactive default. Do not drop the navbar or move buttons left of the explainer.

---
paths:
  - 'resources/js/pages/http/**/*.vue'
  - resources/js/components/HttpNavbar.vue
---

# Http Js Components

## Http page has Errors/Analytics nb-navbar
http/Errors.vue and http/Analytics.vue share HttpNavbar (nb-navbar blue): left side has a contextual nb-navbar-brand title + short description (Errors vs Analytics by route); right side has Errors + Analytics nb-button links (nb-navbar-nav ml-auto) via Wayfinder (errors(), analytics()). Both pages render the same failed logs table and pagination (HttpCheckLogsTable + Pagination); Errors uses usePageHref(errors), Analytics uses usePageHref(analytics). GET /http/errors is ErrorLogsController@index; GET /http/analytics is AnalyticsController@index. GET /http redirects to /http/errors. Active button uses blue, inactive default. Do not drop the navbar or move buttons left of the explainer.

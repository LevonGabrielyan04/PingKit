---
paths:
  - resources/js/composables/usePageHref.ts
---

# Composables

## usePageHref builds paginated Wayfinder hrefs
usePageHref(route) returns pageHref(page): page 1 calls route() with no query; page 2+ calls route({ query: { page } }). Pass any Wayfinder route helper that accepts query options. http/Errors.vue uses usePageHref(errors) for Pagination.

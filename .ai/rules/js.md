---
paths:
  - 'resources/js/**/*.{ts,vue}'
  - 'resources/js/**/*'
---

# Js

## NeoBrutalismCSS is imported globally
NeoBrutalismCSS (`neobrutalismcss`) is a CSS-only UI library imported once in `resources/js/app.ts`. Use its classes in Vue templates without importing the stylesheet in individual Vue files.

## Frontend linting uses Biome
Run `npm run biome:check` (or `biome ci .`) for frontend lint/format in CI via `composer ci:check`. Config lives in `biome.json`; generated Wayfinder paths and `resources/js/components/ui` are excluded like the old ESLint setup. Use `npm run biome:fix` locally to auto-fix.

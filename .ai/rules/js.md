---
paths:
  - 'resources/js/**/*.{ts,vue}'
  - 'resources/js/**/*'
  - 'resources/css/app.css'
  - 'vite.config.ts'
---

# Js

## NeoBrutalismCSS is imported globally
NeoBrutalismCSS (`neobrutalismcss`) is a CSS-only UI library imported once in `resources/css/app.css` via `@import "neobrutalismcss" layer(components)`. Do not import it from `resources/js/app.ts` — JS CSS side-effect imports resolve to `localhost` under `VITE_HMR_HOST` and fail for remote browsers. Use its classes in Vue templates without importing the stylesheet in individual Vue files.

## Frontend linting uses Biome
Run `npm run biome:check` (or `biome ci .`) for frontend lint/format in CI via `composer ci:check`. Config lives in `biome.json`; generated Wayfinder paths and `resources/js/components/ui` are excluded like the old ESLint setup. Use `npm run biome:fix` locally to auto-fix.

## Prefer project-color-theme for element colors
When adding an element color in Vue/JS (status text, highlights, accent colors, etc.), prefer using the global CSS class `project-color-theme` over hardcoded inline colors or other one-off color utilities, unless the design explicitly requires a different color.

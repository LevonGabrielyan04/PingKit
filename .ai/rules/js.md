---
paths:
  - 'resources/js/**/*.{ts,vue}'
  - 'resources/js/**/*'
  - resources/css/app.css
  - vite.config.ts
  - 'resources/js/**/*.{vue,ts}'
---

# Js

## NeoBrutalismCSS is imported globally
NeoBrutalismCSS (`neobrutalismcss`) is a CSS-only UI library imported once in `resources/css/app.css` via `@import "neobrutalismcss" layer(components)`. Do not import it from `resources/js/app.ts` — JS CSS side-effect imports resolve to `localhost` under `VITE_HMR_HOST` and fail for remote browsers. Use its classes in Vue templates without importing the stylesheet in individual Vue files.

## Frontend linting uses Biome
Run `npm run biome:check` (or `biome ci .`) for frontend lint/format in CI via `composer ci:check`. Config lives in `biome.json`; generated Wayfinder paths and `resources/js/components/ui` are excluded like the old ESLint setup. Use `npm run biome:fix` locally to auto-fix.

## Prefer project-color-theme for element colors
When adding an element color in Vue/JS (status text, highlights, accent colors, etc.), prefer using the global CSS class `project-color-theme` over hardcoded inline colors or other one-off color utilities, unless the design explicitly requires a different color.

## Override nb-form-group radio label display
NeoBrutalismCSS `.nb-form-group label { display: block }` overrides `.nb-radio + label { display: inline-flex; align-items: center }`. Keep the unlayered fix in app.css: `.nb-form-group .nb-radio + label { display: inline-flex; align-items: center; margin-bottom: 0 }` so option text stays aligned with the fake radio circle.

## NeoBrutalism overlays use nb-dialog (no modal)
NeoBrutalismCSS has no modal component. For overlays/pop-ups use its Dialog: a root `nb-dialog` (prefer `blue`) with `nb-dialog-header`, `nb-dialog-body`, and an `nb-dialog-footer` that contains a single Hide control (`nb-button`, prefer blue) — no other action buttons. Do not invent modal classes or reach for shadcn/reka Dialog styling on NeoBrutalism surfaces.

## Prefer NeoBrutalism blue theme
Project accent is NeoBrutalismCSS blue (#0077b6), not orange. Prefer the blue variant on nb-button / nb-input / nb-table / nb-dialog / etc. project-color-theme uses #0077b6. App chrome accents in app.css and AppLogo use #0077b6 with white text on filled blue surfaces.

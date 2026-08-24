---
paths:
  - 'resources/js/pages/monitors/**/*.{vue,ts}'
---

# Monitors

## Monitors UI uses NeoBrutalismCSS
Monitor create/edit forms and monitors pages use NeoBrutalismCSS classes (nb-card, nb-input, nb-dropdown, nb-textarea, nb-checkbox, nb-button, nb-radio, nb-form-group), not shadcn/reka form controls from components/ui. Prefer the orange variant to match project-color-theme.

## Monitors index lists; create is a separate page
The monitors Index page lists the current user's monitors and has a New Monitor button that uses the Wayfinder create() route. The create form (MonitorForm) lives on monitors/Create, not Index.

## Monitors index lists in an nb-table
The monitors Index page lists the current user's monitors in a NeoBrutalismCSS table (`nb-table-container` / `nb-table`, orange variant), not a card list. Keep the New Monitor button linking to the Wayfinder create() route.

## Monitors index table has Edit per row
Each monitors Index nb-table row includes an Edit Link (data-test="edit-monitor") to the Wayfinder edit(monitor.id) route. Edit form lives on monitors/Edit using MonitorForm with a monitor prop.

## Monitors row Edit is text link
On the monitors Index nb-table, the per-row Edit control is a plain text Link (project-color-theme + underline), not an nb-button. Keep New Monitor as nb-button orange.

## Monitors row Edit link is black
On the monitors Index nb-table, the per-row Edit text Link uses black text (text-black), not project-color-theme/orange.

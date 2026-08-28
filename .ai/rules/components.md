---
paths:
  - resources/js/components/MonitorForm.vue
  - 'resources/js/components/{AppSidebar,NavMain,NavUser,AppLogo,AppSidebarHeader,Breadcrumbs}.vue'
  - resources/js/components/HttpNavbar.vue
---

# Components

## MonitorForm uses NeoBrutalismCSS
MonitorForm uses NeoBrutalismCSS classes (nb-card, nb-input, nb-dropdown, nb-textarea, nb-checkbox, nb-button, nb-radio, nb-form-group), not shadcn/reka form controls. Prefer the blue variant to match project-color-theme.

## App sidebar uses NeoBrutalismCSS
The app sidebar chrome matches Monitors: NeoBrutalismCSS-style thick black borders, hard offset shadows, blue (#0077b6) active/accent states via nb-app-sidebar / nb-sidebar-link classes in app.css — not soft shadcn sidebar accents. Prefer blue like project-color-theme; keep collapsible Sidebar shell from components/ui.

## Http navbar explainer left, buttons right
HttpNavbar puts Errors title (nb-navbar-brand) + short description on the left and Errors + Download Excel on the right (nb-navbar-nav ml-auto). Errors is an Inertia Link; Download Excel is a plain anchor to errors.export.url() for CSV download.

## Http navbar Download Excel link
HttpNavbar shows Errors (Inertia Link, blue when on /http/errors) and Download Excel (plain <a href={errors.export.url()}>, not Inertia Link — file download). Explainer is static Errors copy; Analytics tab removed.

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
HttpNavbar puts a contextual title (nb-navbar-brand) + short description on the left and Errors/Analytics nb-button links on the right (nb-navbar-nav ml-auto). Title/description switch with the current route (Errors vs Analytics). Keep space-between layout; do not put buttons on the left.

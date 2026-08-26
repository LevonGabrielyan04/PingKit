---
paths:
  - resources/js/components/MonitorForm.vue
  - 'resources/js/components/{AppSidebar,NavMain,NavUser,AppLogo,AppSidebarHeader,Breadcrumbs}.vue'
---

# Components

## MonitorForm uses NeoBrutalismCSS
MonitorForm uses NeoBrutalismCSS classes (nb-card, nb-input, nb-dropdown, nb-textarea, nb-checkbox, nb-button, nb-radio, nb-form-group), not shadcn/reka form controls. Prefer the blue variant to match project-color-theme.

## App sidebar uses NeoBrutalismCSS
The app sidebar chrome matches Monitors: NeoBrutalismCSS-style thick black borders, hard offset shadows, blue (#0077b6) active/accent states via nb-app-sidebar / nb-sidebar-link classes in app.css — not soft shadcn sidebar accents. Prefer blue like project-color-theme; keep collapsible Sidebar shell from components/ui.

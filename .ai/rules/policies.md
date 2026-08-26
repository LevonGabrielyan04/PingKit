---
paths:
  - app/Policies/MonitorPolicy.php
---

# Policies

## MonitorPolicy owns view/update/delete
MonitorPolicy view, update, and delete require $user->id === $monitor->user_id. viewAny and create return true for any authenticated user; listing stays user-scoped in MonitorRepository::index. Call sites: viewAny on index, view on edit, delete on destroy, create/update via Form Requests.

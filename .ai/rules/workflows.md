---
paths:
  - '.github/workflows/**'
---

# Workflows

## Hadolint before composer install
The Hadolint step uses recursive Dockerfile discovery. Run it before `composer setup` (or otherwise exclude vendor), or it will lint Laravel Sail Dockerfiles under vendor/ and fail on their warnings (e.g. DL3016, DL4006, SC2046).

---
paths:
  - phpunit.xml
---

# General

## Tests use MariaDB, not SQLite
The test suite runs against MariaDB, never SQLite in-memory. phpunit.xml forces DB_CONNECTION=mariadb and DB_DATABASE=pingkit_testing so tests never touch the local pingkit database. Host/user/password come from .env (or CI env). Create pingkit_testing locally; docker-compose init and GitHub Actions (mariadb:12.3.2) create it automatically.

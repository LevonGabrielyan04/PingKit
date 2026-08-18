#!/bin/bash
set -euo pipefail

mariadb --user=root --password="${MARIADB_ROOT_PASSWORD}" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS \`pingkit_testing\`;
    GRANT ALL PRIVILEGES ON \`pingkit_testing\`.* TO '${MARIADB_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

#!/bin/bash
set -e

mysql -u root -p${MYSQL_ROOT_PASSWORD} <<-EOSQL
    CREATE DATABASE IF NOT EXISTS lmeve;
    CREATE DATABASE IF NOT EXISTS eve_static_data;
    GRANT ALL PRIVILEGES ON lmeve.* TO '${MYSQL_USER}'@'%';
    GRANT ALL PRIVILEGES ON eve_static_data.* TO '${MYSQL_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

mysql -u root -p${MYSQL_ROOT_PASSWORD} lmeve < /var/www/lmeve/data/schema.sql

#!/bin/bash
set -e

mysql -u root -p${MYSQL_ROOT_PASSWORD} <<-EOSQL
    CREATE DATABASE IF NOT EXISTS lmeve;
    CREATE DATABASE IF NOT EXISTS eve_static_data;
    GRANT ALL PRIVILEGES ON lmeve.* TO '${DB_USER}'@'%';
    GRANT ALL PRIVILEGES ON eve_static_data.* TO '${DB_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

wget https://raw.githubusercontent.com/roxlukas/lmeve/master/data/schema.sql

mysql -u root -p${MYSQL_ROOT_PASSWORD} lmeve < schema.sql

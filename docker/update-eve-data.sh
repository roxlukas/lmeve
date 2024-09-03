#!/bin/bash
set -e

#initialize LMeve database
#make sure to only do it once

LOCK_FILE="schema_installed.lock"

# Port to check
PORT=3306

# Timeout in seconds (adjust as needed)
TIMEOUT=60

echo "Waiting for port $PORT to be open..."

# Loop until the port is open or timeout is reached
until nc -z ${DB_HOST} "$PORT" &> /dev/null; do
  echo "Port $PORT not yet open. Sleeping for 5 seconds..."
  sleep 5
done

echo "Port $PORT is now open!"

if [ -e "$LOCK_FILE" ]; then
    echo "LMeve Schema already imported"
else
    touch "$LOCK_FILE"
    mysql -h ${DB_HOST} -u root -p${MYSQL_ROOT_PASSWORD} <<-EOSQL
    CREATE DATABASE IF NOT EXISTS lmeve;
    CREATE DATABASE IF NOT EXISTS eve_static_data;
    GRANT ALL PRIVILEGES ON lmeve.* TO '${DB_USER}'@'%';
    GRANT ALL PRIVILEGES ON eve_static_data.* TO '${DB_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL
  
    wget https://raw.githubusercontent.com/roxlukas/lmeve/master/data/schema.sql
    
    mysql -h ${DB_HOST} -u root -p${MYSQL_ROOT_PASSWORD} lmeve < schema.sql
    
    #clean up
    rm schema.sql
fi
#update EVE Static Data
wget "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2"
tar -xjf mysql-latest.tar.bz2

# Find the SQL file
SQL_FILE=$(find . -name "*.sql" -type f)

if [ -z "$SQL_FILE" ]; then
    echo "Error: SQL file not found in the archive"
    exit 1
fi

echo "Found SQL file: $SQL_FILE"

# Import the SQL file
mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME_STATIC} < "$SQL_FILE"

# Clean up
rm -rf mysql-latest.tar.bz2 $(dirname "$SQL_FILE")

echo "EVE Static Data updated successfully"

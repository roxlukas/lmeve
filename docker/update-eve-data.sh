#!/bin/bash
set -e

wget "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2"
tar -xjf mysql-latest.tar.bz2 --wildcards --no-anchored 'sql' -C . --strip-components 1
mv *.sql staticdata.sql

mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME_STATIC} < staticdata.sql

rm -f mysql-latest.tar.bz2 staticdata.sql
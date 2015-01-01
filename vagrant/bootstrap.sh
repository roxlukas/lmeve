#!/usr/bin/env bash
export MYSQLPASSWORD=s3cur3PaSsw0rd
	
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password $MYSQLPASSWORD'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password $MYSQLPASSWORD'
    
sudo apt-get -y update
sudo apt-get clean
sudo apt-get -y install mysql-server apache2 php5 php5-cli php5-common php5-mysql libapache2-mod-php5
    
sudo sed -in '/^bind-address/d' /etc/mysql/my.cnf
sudo service mysql restart
    
mysql -uroot -p$MYSQLPASSWORD -e "DROP DATABASE IF EXISTS lmeve"
mysql -uroot -p$MYSQLPASSWORD -e "CREATE DATABASE lmeve"
mysql -uroot -p$MYSQLPASSWORD -e "GRANT USAGE ON *.* TO lmeve@'%'"
mysql -uroot -p$MYSQLPASSWORD -e "DROP USER lmeve@'%'"
mysql -uroot -p$MYSQLPASSWORD -e "CREATE USER lmeve@'%' IDENTIFIED BY 'lmeve#Password'"
mysql -uroot -p$MYSQLPASSWORD -e "GRANT ALL PRIVILEGES ON lmeve.* TO lmeve@'%' WITH GRANT OPTION"

echo "LMeve database 'lmeve' set up with user 'lmeve' and password 'lmeve#Password'"
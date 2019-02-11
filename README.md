<h1>About</h1>

This project was started at the request of Aideron Technologies CEO in 2013. 
Try LMeve's Database module here: http://pozniak.pl/database/index.php
follow lmeve production and get more information: http://pozniak.pl/wp/?tag=lmeve

This app requires EVE Online corporation CEO ESI keys to function.
All Eve Related Materials are Property Of CCP Games

<h3>Please do not contact "Lukas Rox" in game for support, because I do not read eve-mail</h3>
If you find a problem, please open an Issue on GitHub project page: https://github.com/roxlukas/lmeve/issues

<h1>Setup instructions</h1><br>
<br>
1 install lmeve dependancies : <br>
  apt-get : php-mysql, php-pear, apache2, libapache2-mod-php, <br>
            php-cli, php-dev, libyaml-dev, php-mbstring, <br>
            python-yaml, mysql-server, mysql-client, unzip<br>
  <br>
2 Configure Apache2 :<br>
  Alias /lmeve /var/www/lmeve/wwwroot<br>
      <Directory /var/www/lmeve/wwwroot><br>
        Order allow,deny<br>
        Allow from all<br>
        Require all granted <br>
        Options FollowSymLinks<br>
      <  /Directory><br>
<br>
3 Configure MySQL install : <br>
  sudo mkdir /Incoming<br>
  cd /Incoming<br>
  sudo wget "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2.md5"<br>
  tar -xjf mysql-latest.tar.bz2 --wildcards --no-anchored '*sql' -C /Incoming/ --strip-components 1<br>
  sudo mv *.sql /Incoming/staticdata.sql<br>
  sudo mysql<br>
  CREATE DATABASE lmeve;<br>
  CREATE DATABASE EveStaticData;<br>
  USE DATABASE lmeve;<br>
  source /var/www/lmeve/data/schema.sql;<br>
  USE DATABASE EveStaticData;<br>
  source /Incoming/staticdata.sql;<br>
  CREATE USER 'lmeve'@'%' IDENTIFIED BY 'lmpassword';  <-- your custom password here<br>
  GRANT ALL PRIVILEGES ON `lmeve`.* TO 'lmeve'@'%';<br>
  GRANT ALL PRIVILEGES ON `EveStaticData`.* TO 'lmeve'@'%';<br>
  FLUSH PRIVILEGES;<br>
<br>
4 install lmeve core : <br>
     cd /var/www<br>
     sudo git clone https://github.com/roxlukas/lmeve<br>
     cd /var/www/lmeve/config<br>
     cp config-dist.php config.php<br>
     sudo nano config.php <br>
      -configure database settings for your mysql installation<br>
      -add random $lm_salt valueSet up API poller in cron to run every 15 minutes<br>
      */15 * * * * apache2/bin/php -h /var/www/lmeve/bin/poller.php<br>
     <br>
5 install lmeve icons and graphics<br>
    cd /Incoming<br>
    sudo wget "http://content.eveonline.com/data/January2019Release_1.0_Icons.zip"<br>
    sudo wget "http://content.eveonline.com/data/January2019Release_1.0_Types.zip"<br>
    tar -xjf January2019Release_1.0_Icons.zip Icons/Items/ -C /var/www/lmeve/wwwroot/ccp_icons<br>
    tar -xjf January2019Release_1.0_Types.zip /Types/ -C /var/www/lmeve/wwwroot/ccp_img<br>
    <br>
6 Configure CCP Developer application using the lmeve sso config guide :<br>
  https://github.com/roxlukas/lmeve/wiki/Integrating-LMeve-with-EVE-SSO<br>
<br>
7 Finalize installation : <br>
login to lmeve using admin / admin credentials<br>
Wait a few minutes while lmeve parses and alters database tables<br>
Change admin password in Settings<br>
Create a user accout for yourself<br>
Logout, Login with new account<br>
Add corp ESI key in Settings -> ESI Keys<br>


  
<h1>Credits and copyrights</h1>

* LMeve by Lukasz "Lukas Rox" Pozniak

* LMframework v3 by 2005-2014 Lukasz Pozniak

* rixxjavix.css skin by Bryan K. "Rixx Javix" Ward

<h3>Thanks!</h3>

* TheAhmosis and Razeu - it's their idea that I had the pleasure to wrap in code
* Crysis McNally - for excellent ideas and thorough testing
* Aideron Technologies - for excellent closed beta
* CCP Games - for making such a great game and providing API for us, developer kind, to tinker with
* To all supporters and donators. Thank you!

<h3>Donations are welcome!</h3>

According to CCP Developers License paragraph 4 section 4 (https://developers.eveonline.com/resource/license-agreement)
you can buy me a coffe or help fund the server.

If you'd like to support the development, feel free to do so: https://www.paypal.me/roxlukas

<h4>Top donators:</h4>
Starfire Dai, Crysis McNally


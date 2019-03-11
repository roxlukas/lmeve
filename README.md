<h1>About</h1>
This project was started at the request of Aideron Technologies CEO in 2013. This software is basically an advanced prototype.
Code beauty was not a priority, moreover this is not in objective PHP, just plain-old structural PHP.
I had plans to refactor entire project into CodeIgniter framework, but this plan is currently on hold.

More information: http://pozniak.pl/wp/?tag=lmeve

Official Discord channel: https://discord.gg/9yBhuPd

<h3>Please do not contact "Lukas Rox" in game for support, because he does not play eve </h3>
If you find a problem, please come to official Discord channel here https://discord.gg/9yBhuPd 
and/or open an Issue on GitHub project page: https://github.com/roxlukas/lmeve/issues

Try the LMeve Database module here: http://pozniak.pl/database/index.php
follow lmeve production and get more information: http://pozniak.pl/wp/?tag=lmeve

This app requires EVE Online corporation CEO ESI keys to function.
All Eve Related Materials are Property Of CCP Games

<h3>Please do not contact "Lukas Rox" in game for support, because I do not read eve-mail</h3>
If you find a problem, please open an Issue on GitHub project page: https://github.com/roxlukas/lmeve/issues

<h1>Setup instructions</h1>


Steps for installing LMEVE : <br>
	1. install LmEvE core<br>
	2. install dependancies<br>
	3. configure apache2<br>
	4. configure mysql<br>
	5. lmeve graphics<br>
	6. Registering with ccp<br>
	7. Finalization<br>
	<br>
1 install lmeve core : <br>
  cd /var/www<br>
  sudo git clone https://github.com/roxlukas/lmeve<br>
    	  <br>
	  <br>
2 install lmeve dependancies : <br>

  sudo apt-get install php-mysql php-pear apache2 libapache2-mod-php<br>
            php-cli php-dev libyaml-dev, php-mbstring <br>
            python-yaml mysql-server mysql-client unzip<br>
  <br>
  <br>
3 Configure Apache2 : <br>
	sudo nano /etc/apache2/sites-enabled/000-default.conf<br>
	change DocumentRoot to : /var/www/lmeve/wwwroot<br>
	<br>
	  <br>
4 Configure MySQL install : <br>
	sudo mkdir /Incoming <br>
	cd /Incoming <br>
	sudo wget "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2" <br>
	tar -xjf mysql-latest.tar.bz2 --wildcards --no-anchored '*sql' -C /Incoming/ --strip-components 1 <br>
	sudo mv *.sql /Incoming/staticdata.sql <br>
	sudo mysql <br>
	CREATE DATABASE lmeve; <br>
	CREATE DATABASE EveStaticData; <br>
	USE lmeve;<br>
	source /var/www/lmeve/data/schema.sql;<br>
	USE EveStaticData;<br>
	source /Incoming/staticdata.sql;<br>
	CREATE USER 'lmeve'@'%' IDENTIFIED BY 'lmpassword';  		//<-- your custom password here<br>
	GRANT ALL PRIVILEGES ON `lmeve`.* TO 'lmeve'@'%';    		// Change % to your lmeve internal network address<br>
	GRANT ALL PRIVILEGES ON `EveStaticData`.* TO 'lmeve'@'%'; // Change % to your lmeve internal network address<br>
	FLUSH PRIVILEGES;<br>
<br>
    <br> 
5 install lmeve icons and graphics <br>
 //remove placeholder ccp icon and img folders, download image package <br>
  cd /var/www/lmeve/wwwroot <br>
  sudo rm -fr ccp_icons ccp_img <br>
  cd /Incoming <br>
  sudo wget www.ash-online.net/lmevegfx/lmevegfx.tar.gz <br>
  sudo tar -zjvf lmevegfx.tar.gz -C / <br>


6 Configure CCP Developer application using the lmeve sso config guide : <br>
  https://github.com/roxlukas/lmeve/wiki/Integrating-LMeve-with-EVE-SSO <br>


7 Finalize installation : <br>
	cd /var/www/lmeve/config <br>
	sudo nano config-dist.php  <br>
	edit the config file and save it as config.php  <br>
  Set up API poller in cron to run every 15 minutes -   */15 * * * * apache2/bin/php -h /var/www/lmeve/bin/poller.php <br>
	login to lmeve using admin / admin credentials and wait a few minutes while lmeve parses and alters database tables <br>
	Change admin password in Settings <br>
	Create a user accout for yourself <br>
	Logout, Login with your new account <br>
	Add corp ESI key in Settings -> ESI Keys <br>

  
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


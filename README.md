<h1>About</h1>

This project was started at the request of Aideron Technologies CEO in 2013. This software is basically an advanced prototype.
Code beauty was not a priority, moreover this is not in objective PHP, just plain-old structural PHP.
I had plans to refactor entire project into CodeIgniter framework, but this plan is currently on hold.

More information: http://pozniak.pl/wp/?tag=lmeve

This app requires EVE Online corporation API keys to function. It doesn't use CREST, which came after I retired from EVE Online.

All Eve Related Materials are Property Of CCP Games

<h1>Setup instructions</h1>

There is no installer currently, so there is some setup work to get LMeve to run

<h3>1. Initial setup</h3>

* Go to ./config/ directory, copy config-dist.php to config.php and set it up according to your host
* After setting up new SALT value in config.php, generate admin password hash by using php ./bin/passwd.php
* copy the password hash to clipboard
* Download current `Types` and `Icons` from EVE Online Toolkit page: http://community.eveonline.com/community/fansites/toolkit/
* unpack all PNG files from `Types` to `./wwwroot/ccp_img/`
* unpack all PNG files from `Icons` to `./wwwroot/ccp_icons/`

<h3>2. Database setup</h3>

* Import `./data/schema.sql` file before using LMeve. Remember to set the db config options in `./config/config.php`
* Import latest static data dump (can be in other db schema for clarity, for example lmeve db in `lmeve` and static data in `sde_rubicon`)
You can download latest static data from Steve Ronuken's website: https://www.fuzzwork.co.uk/dump/
* If necessary, change table names to lowercase using script `./data/rename-lowercase.sql` - ToDo: add file
* Access the database using phpmyadmin or other tool, go to `lmusers` table
* Edit record for user 'admin'
* Paste the password hash from clipboard in 'pass' field
then you can login to application using admin/admin
password should be be changed in 'Settings' later.
* Add API key to `cfgapikeys` table. keyID goes to `keyID`, vCode goes to `vCode`

<h3>3. API Poller setup</h3>

* Set up API poller in cron to run every 15 minutes

  `*/15 * * * * 	[path-to-php]/php [path-to-lmeve]/bin/poller.php`
  
<h1>Credits and copyrights</h1>

* LMeve by Lukasz "Lukas Rox" Pozniak

* LMframework v3 by 2005-2013 Lukasz Pozniak

* rixxjavix.css skin by Bryan K. "Rixx Javix" Ward
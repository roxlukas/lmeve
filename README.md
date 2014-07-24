<h1>About</h1>

This project was started at the request of Aideron Technologies CEO in 2013. This software is basically an advanced prototype.
Code beauty was not a priority, moreover this is not in objective PHP, just plain-old structural PHP.
I had plans to refactor entire project into CodeIgniter framework, but this plan is currently on hold.

Current version for EVE: Crius is 0.1.40 (check the "Releases" page). The trunk code is unstable and shouldn't be used in production.

Known issue in 0.1.40: Kits feature isn't working properly. Patch will be released soon.

More information: http://pozniak.pl/wp/?tag=lmeve

Try LMeve's Database module here: http://pozniak.pl/database/index.php

This app requires EVE Online corporation API keys to function. It doesn't use CREST, which came after I retired from EVE Online.

All Eve Related Materials are Property Of CCP Games

<h1>Setup instructions</h1>

There is no installer currently, so there is some setup work to get LMeve to run

<h3>1. Initial setup</h3>

`index.php` and the website itself is in `./wwwroot/` directory. If you can set up your webserver root to this directory, please do so.

* Go to `./config/ directory`, copy `config-dist.php` to `config.php` and set it up according to your host
* After setting up new `$LM_SALT` value in `config.php`, generate admin password by using `php ./bin/passwd.php`
* Download current `Types` and `Icons` from EVE Online Toolkit page: http://community.eveonline.com/community/fansites/toolkit/
* unpack all PNG files from `Types` to `./wwwroot/ccp_img/`
* unpack all PNG files from `Icons` to `./wwwroot/ccp_icons/`

<h3>2. Database setup</h3>

* Import `./data/schema.sql` file to MySQL database before using LMeve. Remember to set the db config options in `./config/config.php`
* Import latest static data dump (can be put in different db schema for clarity, for example you can import lmeve db to `lmeve` schema and static data in `sde_crius`. LMeve will always use SDE schema set in `$LM_EVEDB` variable in `config.php` file)
You can download latest static data from Steve Ronuken's website: https://www.fuzzwork.co.uk/dump/
* Copy all YAML files (the yare included in Steve Ronuken's mysql package, or from official Static Data Export) to `./data/<static_data_schema_name>/`
* Run `php ./bin/update_yaml.php`
* Add corp API key (or keys) to `cfgapikeys` table. keyID goes to `keyID`, vCode goes to `vCode`
* Full corp API key works best, but the app will adjust the amount of visible information according to the rights it has been given.

Afterwards you will be able to login as admin/admin
password should be be changed in `Settings` as soon as possible.

<h3>3. API Poller setup</h3>

* Set up API poller in cron to run every 15 minutes

  `*/15 * * * * 	[path-to-php]/php [path-to-lmeve]/bin/poller.php`
  
<h1>Credits and copyrights</h1>

* LMeve by Lukasz "Lukas Rox" Pozniak

* LMframework v3 by 2005-2014 Lukasz Pozniak

* rixxjavix.css skin by Bryan K. "Rixx Javix" Ward

<h3>Thanks!</h3>

* TheAhmosis and Razeu - it's their idea that I had the pleasure to wrap in code
* Crysis McNally - for excellent ideas and thorough testing
* Aideron Technologies - for excellent closed beta
* CCP Games - for making such a great game and providing API for us, developer kind, to tinker with
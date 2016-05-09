<?php
//APP NAME - for example "Aideron Technologies' LMeve"
$LM_APP_NAME='LMeve';
//locked
$LM_LOCKED=0;
//read-only
$LM_READONLY=0;
//check for session IP changes
$LM_IPCONTROL=1;
//maximum session cookie time
$LM_SESSION=3600;
//cookie path. must have a trailing slash, for example: /lmeve/
$LM_COOKIEPATH='/';
//debug database queries and PHP errors? (include additional information in error messages)
$LM_DEBUG=FALSE;
//which DB engine to use? - MySQL be default, can work with PostgreSQL if needed.
$LM_DBENGINE='MYSQL'; //MYSQL || PGSQL
//database settings
$LM_dbhost='localhost';
$LM_dbname='lmeve';
$LM_dbuser='lmeve';
$LM_dbpass='password';
//LMeve will use static data from this database schema. Can be the same as LMeve $LM_dbname,
//but for the sake of easy SDE updates a different db schema is recommended
$LM_EVEDB='eve_tia100_dbo';
//salt used for passwords. Should be a random string CHANGE IT!
$LM_SALT='abcde12345';
//thousand and decimal separators
$THOUSAND_SEP=",";
$DECIMAL_SEP=".";
//default CSS style
$LM_DEFAULT_CSS="css/rixxjavix.css";
//force SSL
$LM_FORCE_SSL=FALSE;
//use CSRF tokens in forms
$LM_SECUREFORMS=TRUE;
//use EVE SSO - see https://wiki.eveonline.com/en/wiki/EVE_SSO_Documentation
$SSOENABLED=FALSE;
$SSO_REDIRECT_URL='https://lmeve.com/ssologin.php';
$SSO_CLIENT_ID='sso_client_id';
$SSO_CLIENT_SECRET='sso_client_secret';
//Auth server can be either login.eveonline.com for Tranquility, or sisilogin.testeveonline.com when trying to use Sisi.
$SSO_AUTH_SERVER='sisilogin.testeveonline.com';
$API_BASEURL="https://api.eveonline.com"; 
$CREST_BASEURL="https://crest-tq.eveonline.com";
//CSRF token expiry time (in seconds)
$LM_SECUREFORMSEXPIRY=300;
//Buy calculator can show colored hints green - we buy, yellow - we have enough, red - we have way more than enough - we dont buy
$LM_BUYCALC_SHOWHINTS=TRUE;
//for LDAP authentication use the following settings
$LM_LDAP_USE = false;
$LM_LDAP_UID = "uid="; //for Windows: "" || for Linux: "uid="
$LM_LDAP_DOMAIN = ",ou=people,dc=diameter,dc=local"; //for Windows: @domain.company.com || for Linux: ,ou=people,dc=diameter,dc=local
$LM_LDAP_HOSTS = array("192.168.0.1");
//table with usernames and passwords for internal authentication
$USERSTABLE='lmusers';
//should LMeve learn new rights 1 for development, 0 for production
$LM_LEARNING_MODE=0;
//use proxy for CCP WebGL assets
$LM_CCPWGL_USEPROXY=FALSE;
//cache files proxied by WebGL proxy
$LM_CCPWGL_PROXYCACHE=FALSE;
//log every and all attempts to use proxy
$LM_CCPWGL_PROXYAUDIT=FALSE;
//database schema for WebGL proxy cache
$LM_CCPWGL_CACHESCHEMA='lmeve-cache';
//CCP CDN URL - normally it should never be changed
$LM_CCPWGL_URL='https://web.ccpgamescdn.com/ccpwgl/res/';
//TODO: Make the below 2 variables values in a database table or something maybe?
//What EVE Central price to use for profit explorer manufacturing costs
$EC_PRICE_TO_USE_FOR_MAN=array('type' => 'sell', 'price' => 'min');
//What EVE Central price to use for profit explorer market price
$EC_PRICE_TO_USE_FOR_SELL=array('type' => 'sell', 'price' => 'min');
?>

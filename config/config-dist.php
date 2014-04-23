<?
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
$LM_COOKIEPATH='/lmeve/wwwroot/';
//debug database queries? (include additional information in error messages)
$LM_DEBUG=1;
$LM_DBENGINE='MYSQL'; //MYSQL || PGSQL
//database settings
$LM_dbhost='localhost';
$LM_dbname='lmeve';
$LM_dbuser='lmeve';
$LM_dbpass='password';
//salt used for passwords. Should be a random string CHANGE IT!
$LM_SALT='abcde12345';
//thousand and decimal separators
$THOUSAND_SEP=",";
$DECIMAL_SEP=".";
//default CSS style
$LM_DEFAULT_CSS="css/rixxjavix.css";
//force SSL
$LM_FORCE_SSL=FALSE;
//LMeve will use static data from this database. Static data table names should be in lowercase!
$LM_EVEDB='eve_rub130_dbo';
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
//CCP CDN URL - normally it should never be changed
$LM_CCPWGL_URL='https://web.ccpgamescdn.com/ccpwgl/res/';
?>

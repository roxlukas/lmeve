<?php
set_include_path("../include");
date_default_timezone_set(@date_default_timezone_get());
if (!is_file('../config/config.php')) die ($LANG['CONFIGERROR']);
include_once('../config/config.php'); //load config file
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization
include_once("lang.php");  //translations
include_once("menu.php");  //menu
include_once("template.php");  //templates
include_once("csrf.php");  //anti-csrf token implementation (secure forms)
include_once('configuration.php'); //configuration settings in db
include_once('hooks.php'); //hooks - login hook
include_once("ssofunctions.php"); //SSO functions

if (!is_file('../config/config.php')) die('Config file not found.');
 
//Ustawienie sesji i parametrów ciastka sesyjnego
$param=session_get_cookie_params();
session_set_cookie_params($LM_SESSION,$LM_COOKIEPATH,$param['domain'],$LM_COOKIESECUREONLY,true);
session_start();

//Security enhancement - regenerate session after HTTPS redirect
if ($_SESSION['regenerateID']===true) {
    session_regenerate_id(true);
    $_SESSION=array();
}

check_changed_session_ip(); //CHECK IF THE IP DID NOT CHANGE DURING SESSION
check_changed_session_path(); //CHECK IF COOKIEPATH HAS CHANGED DURING SESSION

//check if we force HTTPS, and if we do, check if we are indeed on HTTPS
//if not, redirect user to HTTPS
if($LM_FORCE_SSL && $_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    $_SESSION['regenerateID']=true;
    exit();
}

//SSO FUNCTIONS
//moved to "ssofunctions.php"


//SSO ALGORITHM

if (!$SSOENABLED) { //if sso is not enabled, exit immediately
    template_locked("EVE SSO is not available.");
    die();
} else if ($LM_LOCKED==1) { //if LMeve is in locked mode
        template_locked();
} else if ($_SESSION['status']==0) { //if session is not in logged state
    $code=$_GET['code'];
    if (!isset($code)) {
        //first redirect the user to SSO website
        redirect_to_sso();
    } else {
        //secondly, receive callback from SSO AUTH SERVER
        $state=get_sso_state();
        if (!token_verify($state)) {
            //error occured, bail
            template_locked("Invalid incoming redirect from SSO login site.");
            die();
        }
        //we have the code now, lets get the login token
        $token=get_login_token($code);
        //check if we got a valid Bearer token
        if (!(isset($token->access_token) && isset($token->token_type) && isset($token->expires_in) && $token->token_type=='Bearer' && $token->expires_in>0)) {
            //problem with token, bail!
            template_locked("Invalid Bearer token received from SSO login site.");
            die();
        }
        //we've got a valid token, let's fetch the characterID 
        $verify=verify_token($token);

        if (!(isset($verify->CharacterID) && isset($verify->CharacterName) && isset($verify->TokenType))) {
            //problem with verify, bail!
            template_locked("Invalid Verify response received from SSO login site.");
            die();
        }
        
        //ok, now let's see if this character is known to LMeve
        $userID=get_userID($verify->CharacterID);
        if ($userID===false) {
            //unkown character, bail!
            template_locked("This EVE Online character is not authorized.<br/><br/><i>If the character should be authorized, check whether it's linked to your LMeve account.</i>");
            die();
        }
        //now check the characterOwnerHash
        if (!checkOwnerHash($verify->CharacterID,$verify->CharacterOwnerHash)) {
            template_locked("This EVE Online character has recently changed owner. Please contact administrator.");
            die();
        }
        //everything green! authorize user.
        $_SESSION["granted"]=$userID;
        $_SESSION["status"]=1;
        //LOGIN HOOK
        login_hook();
        $_SESSION['LOGIN_REALM']='EVE_SSO';
        updatelast(date('d.m.Y G:i'),$_SERVER['REMOTE_ADDR']);
        header("Location: index.php");
    }
} else if ($_SESSION['status']==1 && $_SESSION['ssomode'] == "addkey") { //Add ESI token
    $code=$_GET['code'];
    if (!isset($code)) {
        //first redirect the user to SSO website + set required ESI scopes!
        redirect_to_sso(getLMeveCorpScopes());
    } else {
        //secondly, receive callback from SSO AUTH SERVER
        $state=get_sso_state();
        if (!token_verify($state)) {
            //error occured, bail
            template_locked("Invalid incoming redirect from SSO login site.");
            die();
        }
        //echo("DEBUG: token_verify()<br/>");
        //we have the code now, lets get the login token
        $token=get_login_token($code);
        //check if we got a valid Bearer token
        if (!(isset($token->access_token) && isset($token->token_type) && isset($token->expires_in) && $token->token_type=='Bearer' && $token->expires_in>0)) {
            //problem with token, bail!
            template_locked("Invalid Bearer token received from SSO login site.");
            die();
        }
        //we've got a valid token, let's fetch the characterID 
        $verify=verify_token($token);
        //check if required fileds are set
        if (!(isset($verify->CharacterID) && isset($verify->CharacterName) && isset($verify->TokenType))) {
            //problem with verify, bail!
            template_locked("Invalid Verify response received from SSO login site.");
            die();
        }
        //check if the Scopes are correct for LMeve
        if (!compareScopes($verify->Scopes, getLMeveCorpScopes())) {
            template_locked("Available Scopes do not match required Scopes.<br/>Make sure to generate Application with correct Scopes on https://developers.eveonline.com/applications.");
            die();
        }
        //save refresh_token in LMeve database, so we can get Authorization token in poller later
        $sql="INSERT INTO `cfgesitoken` VALUES (DEFAULT, '$token->refresh_token');";
        
        db_uquery($sql);
        
        template_locked("Saving ESI token in LMeve database...");
        //unset ssomode to default
        unset($_SESSION['ssomode']);
        ?><script type="text/javascript">location.href="index.php?id=5&id2=21";</script><?php
    }
}else if ($_SESSION['status']==1) { //LOGGED ON
    header("Location: index.php");
}
    
?>

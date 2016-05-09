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

function redirect_to_sso() {
    global $SSO_AUTH_SERVER, $SSO_CLIENT_ID,$SSO_REDIRECT_URL;
    $SSO_NONCE=token_generate(TRUE);
    header("Location: https://$SSO_AUTH_SERVER/oauth/authorize/?response_type=code&redirect_uri=$SSO_REDIRECT_URL&client_id=$SSO_CLIENT_ID&scope=&state=$SSO_NONCE");
}

function get_sso_state() {
    return str_replace(' ','+',$_GET['state']);
}

function get_login_token($code) {
    global $SSO_AUTH_SERVER, $SSO_CLIENT_ID,$SSO_CLIENT_SECRET;
    $AUTH=base64_encode("$SSO_CLIENT_ID:$SSO_CLIENT_SECRET");
        $postdata = http_build_query(
            array(
                'grant_type' => 'authorization_code',
                'code' => $code
            )
        );
        $CTX_TOKEN = stream_context_create(array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"POST",
                    'header'=>"User-Agent: LMeve/1.0 SSO Client Version/1\r\n".
                        "Authorization: Basic $AUTH\r\n".
                        "Content-Type: application/x-www-form-urlencoded\r\n".
                        "Host: $SSO_AUTH_SERVER",
                    'content' => $postdata
                 )
            ));
        $json_token=file_get_contents("https://$SSO_AUTH_SERVER/oauth/token", FALSE, $CTX_TOKEN);
        $token=json_decode($json_token);
        return $token;
}

function verify_token($login_token) {
    global $SSO_AUTH_SERVER;
    $TOKEN=$login_token->access_token;
        $CTX_VERIFY = stream_context_create(array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"GET",
                    'header'=>"User-Agent: LMeve/1.0 SSO Client Version/1\r\n".
                        "Authorization: Bearer $TOKEN\r\n".
                        "Host: $SSO_AUTH_SERVER"
                 )
            ));
        $json_verify=file_get_contents("https://$SSO_AUTH_SERVER/oauth/verify", FALSE, $CTX_VERIFY);
        $verify=json_decode($json_verify);
        return $verify;
}

function checkOwnerHash($charID,$ownerHash) {
    //when the character logs in for the first time, save the ownerHash and return true
    //when ownerHash exists for the character, compare it. If it's the same, return true, if it's different, return false
    $hash=db_asocquery("SELECT * FROM `lmownerhash` WHERE `characterID`=$charID;");
    if (count($hash)==0) {
        db_uquery("INSERT INTO `lmownerhash` VALUES ($charID,'$ownerHash');");
        return TRUE;
    } else {
        if ($hash[0]['ownerHash']==$ownerHash) return TRUE; else return FALSE;
    }
}

function get_userID($charID) {
    //we ignore ownerHash at the moment, tbd later
    global $USERSTABLE;
        $sql="SELECT lmu.`userID`
                FROM `lmchars` lmc
                JOIN `apicorpmembers` acm
                ON lmc.`charID`=acm.`characterID`
                JOIN `$USERSTABLE` lmu
                ON lmc.`userID`=lmu.`userID`
                WHERE lmc.`charID`=$charID AND lmu.`act`=1;";
        $char=db_query($sql);
        if (count($char)==1) {
            return $char[0][0];
        } else return false;
}

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
} else if ($_SESSION['status']==1) { //LOGGED ON
    header("Location: index.php");
}
    
?>

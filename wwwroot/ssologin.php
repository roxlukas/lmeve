<?php
set_include_path("../include");
date_default_timezone_set("Europe/Paris");
if (!is_file('../config/config.php')) die ($LANG['CONFIGERROR']);
include_once('../config/config.php'); //load config file
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization
include_once("lang.php");  //translations
include_once("menu.php");  //menu
include_once("template.php");  //templates
include_once("csrf.php");  //anti-csrf token implementation (secure forms)

if (!is_file('../config/config.php')) die('Config file not found.');
 
//Ustawienie sesji i parametrów ciastka sesyjnego
$param=session_get_cookie_params();
session_set_cookie_params($LM_SESSION,$LM_COOKIEPATH,$param['domain'],$LM_COOKIESECUREONLY,true);
session_start();

//POPRAWKI BEZPIECZEŃSTWA - regenrowanie ID sesji po przekirowaniu HTTP->HTTPS
if ($_SESSION['regenerateID']===true) {
    session_regenerate_id(true);
    $_SESSION=array();
}

check_changed_session_ip(); //CHECK IF THE IP DID NOT CHANGE DURING SESSION
check_changed_session_path(); //CHECK IF COOKIEPATH HAS CHANGED DURING SESSION
//token_generate();

//check if we force HTTPS, and if we do, check if we are indeed on HTTPS
//if not, redirect user to HTTPS
if($LM_FORCE_SSL && $_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    $_SESSION['regenerateID']=true;
    exit();
}

if (!$SSOENABLED) {
    template_locked("EVE SSO is not available.");
    die();
} else {
    if ($LM_LOCKED==1) { //APP IS LOCKED!
        template_locked();
    } else { 			 //APP NOT LOCKED
        if ($_SESSION['status']==0) { //NOT LOGGED ON
            $code=$_GET['code'];
            if (!isset($code)) {
                //first redirect the user to SSO website
                $SSO_NONCE=token_generate(TRUE);
                header("Location: https://$SSO_AUTH_SERVER/oauth/authorize/?response_type=code&redirect_uri=$SSO_REDIRECT_URL&client_id=$SSO_CLIENT_ID&scope=&state=$SSO_NONCE");
            } else {
                //secondly, receive callback from SSO AUTH SERVER
                $state=str_replace(' ','+',$_GET['state']);
                if (!token_verify($state)) {
                    //error occured, bail
                    template_locked("Invalid incoming redirect from SSO login site.");
                    die();
                }
                //we have the code now, lets get the login token
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
                //check if we got a valid Bearer token
                if (!(isset($token->access_token) && isset($token->token_type) && isset($token->expires_in) && $token->token_type=='Bearer' && $token->expires_in>0)) {
                    //problem with token, bail!
                    template_locked("Invalid Bearer token received from SSO login site.");
                    die();
                }
                //we've got a valid token, let's fetch the characterID
                $TOKEN=$token->access_token;
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
                
                if (!(isset($verify->CharacterID) && isset($verify->CharacterName) && isset($verify->TokenType))) {
                    //problem with verify, bail!
                    template_locked("Invalid Verify response received from SSO login site.");
                    die();
                }
                //ok, let's see if this character is known to us
                $CHARACTERID=$verify->CharacterID;
                $char=db_query("SELECT lmu.`userID`
                        FROM `lmchars` lmc
                        JOIN `apicorpmembers` acm
                        ON lmc.`charID`=acm.`characterID`
                        JOIN `$USERSTABLE` lmu
                        ON lmc.`userID`=lmu.`userID`
                        WHERE lmc.`charID`=$CHARACTERID AND lmu.`act`=1;");
                if (!count($char)==1) {
                    //unkown character, bail!
                    template_locked("Unknown characterID. Is the selected character linked with your LMeve account?");
                    die();
                }
                $user=$char[0][0];
                //authorize user!
                $_SESSION["granted"]=$user;
		$_SESSION["status"]=1;
		updatelast(date('d.m.Y G:i'),$_SERVER['REMOTE_ADDR']);
                header("Location: index.php");
            }
             
        } else if ($_SESSION['status']==1) { //LOGGED ON
            header("Location: index.php");
        }
    }
}
?>

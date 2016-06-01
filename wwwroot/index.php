<?php
/**********************************************************************************
								LM Framework v3
								
	A simple PHP based application framework.
	
	Contact: pozniak.lukasz@gmail.com
	
	Copyright (c) 2005-2013, �ukasz Po�niak
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
	
	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

**********************************************************************************/
header('Content-Type: text/html; charset=UTF-8');

mb_internal_encoding('UTF-8'); 
mb_http_output('UTF-8'); 
mb_http_input('UTF-8'); 
mb_regex_encoding('UTF-8'); 

set_include_path("../include");
date_default_timezone_set(@date_default_timezone_get());
if (!is_file('../config/config.php')) die('Config file not found.');
include_once('../config/config.php'); //load config file
if ($LM_DEBUG==TRUE) error_reporting(E_ALL ^ E_NOTICE); else error_reporting(0);
include_once("db.php");  //db access functions
include_once("log.php");  //logging facility
include_once('auth.php'); //authentication and authorization
include_once("lang.php");  //translations
include_once("menu.php");  //menu
include_once("template.php");  //templates
include_once("csrf.php");  //anti-csrf token implementation (secure forms)
include_once('configuration.php'); //configuration settings in db
include_once('mobile.php'); //mobile device related functions
include_once('hooks.php'); //hooks - login hook

$lmver="0.1.57 beta";
 
//setting session cookie params
$param=session_get_cookie_params();
session_set_cookie_params($LM_SESSION,$LM_COOKIEPATH,$param['domain'],$LM_COOKIESECUREONLY,true);
session_start();
//this prevents a forced logout after $LM_SESSION seconds
//so the user session always lasts $LM_SESSION seconds after the last action in LMeve
setcookie(session_name(),session_id(),time()+$LM_SESSION);

//Security addon - regenerate session ID after HTTP->HTTPS redirect
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
    //Security addon - regenerate session ID after HTTP->HTTPS redirect
    $_SESSION['regenerateID']=true;
    exit();
}
//echo("PHP_SESS_ID=".session_id()." regenerateID=".$_SESSION['regenerateID']);

if (isMobileUserAgent()) $MOBILE=1; else $MOBILE=0;

//
//AUTOLOGIN HERE IF YOU WANT
//set the POST variable to the desired username and password
//
if ($LM_LOCKED==1) { //APP IS LOCKED!
	$MOBILE ? mobile_template_locked() : template_locked();
} else { 			 //APP NOT LOCKED
	if ($_SESSION['status']==0) { //NOT LOGGED ON
		if (empty($_POST['login'])&&empty($_SESSION["status"])) { //NO LOGIN DATA? DISPLAY PROMPT
                        $MOBILE ? mobile_template_login() : template_login();
		} else { //FILLED DATA? CHECK CREDENTIALS
                        //if user table is empty, reset admin password
                        resetAdminPassword();
                        //check authorization
			$granted=auth_user(addslashes($_POST['login']),addslashes($_POST['password']));
			if ($granted>-1) { //LOGIN SUCCESS?
				$_SESSION["granted"]=$granted;
				$_SESSION["status"]=1;
                                //LOGIN HOOK
                                login_hook();
				//MAIN WINDOW
				updatelast(date('d.m.Y G:i'),$_SERVER['REMOTE_ADDR']);
                                $MOBILE ? mobile_template_main() : template_main();
			} else { //LOGIN FAILURE?
				//WRITE TO LOG FILE
				$uzytk=htmlspecialchars($_POST['login']);
				$do_logu=sprintf("<b>Bad logon!</b> login: <b>%s</b>.",$uzytk);
				loguj("../var/access.txt",$do_logu);
				$_SESSION=array();
				//DISPLAY BAD LOGON
                                $MOBILE ? mobile_template_badlogon() : template_badlogon();
			}
		}
	} else if ($_SESSION['status']==1) { //LOGGED ON
            check_expired_accounts();
		if ($_GET['logoff']==1) { //TRYING TO LOG OUT?
			$_SESSION=array();
			$MOBILE ? mobile_template_logout() : template_logout();
		} else { //MAIN WINDOW
			updatelast(date('d.m.Y G:i'),$_SERVER['REMOTE_ADDR']);
			$MOBILE ? mobile_template_main() : template_main();
		}
	}
}
?>
<?php
//$granted=-1;
include_once("ldap.php");
include_once("db.php");
include_once("log.php");
include_once("auth.php");
include_once("dbcatalog.php");

function check_includes_for_auth_php() {
	return TRUE;
}

function checksession() {
	if ($_SESSION['status']!=1) die("Wrong script call. <a href=\"index.php\">$LM_APP_NAME</a>");       
}

function check_expired_accounts() {
    global $USERSTABLE,$MOBILE;
	if (isset($_SESSION['granted'])) {
		$sql="SELECT `act` FROM `$USERSTABLE` WHERE `userID`=${_SESSION['granted']};";
		$result=db_query($sql);
		if ($result[0][0]==0) { 
                    $_SESSION=array();
                    $MOBILE ? mobile_template_logout('This session has expired.') : template_logout('This session has expired.');
                    die();
                }
	}
}

function check_changed_session_ip() {
	global $LM_IPCONTROL,$LANG;
	if ($LM_IPCONTROL==1) {
		$current_ip=$_SERVER['REMOTE_ADDR'];
		if ((isset($_SESSION['ip'])) && $_SESSION['ip'] != $current_ip) { //jest ustawiony, ale sie rozni
			$do_logu="<b>IP Address has changed during session lifetime</b> Should be: ${_SESSION['ip']}";
			loguj("../var/access.txt",$do_logu);
			$_SESSION['status']=0;
			$_SESSION['granted']=-1;
			unset($_SESSION['ip']);
			die($LANG['RELOGIN']);
		} else { //nie jest ustawiony lub sie nie rozni
			$_SESSION['ip']=$current_ip;
		}
	} else {
		unset($_SESSION['ip']);
	}
}

function getUserCount() {
    global $USERSTABLE;
    $c=db_asocquery("SELECT `userID` FROM `$USERSTABLE`;");
    if ($c !== FALSE ) return count($c); else return FALSE;
}

function resetAdminPassword() {
    global $USERSTABLE,$LM_DEFAULT_CSS;
    $pwd=hashpass("admin");
    $lockfile=__DIR__.'/../INSTALL';
    if (!file_exists($lockfile)) return FALSE;
    if (getUserCount()===0) {
        //echo("User 'admin' does not exist. Adding user 'admin' and setting password 'admin'".PHP_EOL);
            $userid=db_query("SELECT MAX(`userID`) FROM `$USERSTABLE`;");
            $userid=$userid[0][0]; $userid++;
        db_uquery("INSERT INTO `$USERSTABLE` VALUES ($userid, 'admin', '$pwd', '127.0.0.1', '01.01.2007 12:00',0,'$LM_DEFAULT_CSS',1);");
        //echo("Adding 'Administrator' role to user 'admin'".PHP_EOL);
            $roleid=db_query("SELECT `roleID` FROM `lmroles` WHERE `roleName`='Administrator';");
            $roleid=$roleid[0][0];
        db_uquery("INSERT IGNORE INTO `lmuserroles` VALUES ($userid,$roleid);");
    } else {
        //echo("User 'admin' already exists. New password is 'admin'".PHP_EOL);
        db_uquery("UPDATE `$USERSTABLE` SET `pass`='$pwd',`act`=1 WHERE login='admin';");
    }
    return TRUE;
}

function check_changed_session_path() {
    global $LM_COOKIEPATH;
    $current_path=$LM_COOKIEPATH;
		if ((isset($_SESSION['path'])) && $_SESSION['path'] != $current_path) { //jest ustawiony, ale sie rozni
			$do_logu="<b>Session cookiepath has changed</b>. It is $current_path, but should be: ${_SESSION['path']}";
			loguj("../var/access.txt",$do_logu);
			$_SESSION['status']=0;
			$_SESSION['granted']=-1;
			unset($_SESSION['path']);
			die($LANG['RELOGIN']);
		} else { //nie jest ustawiony lub sie nie rozni
			$_SESSION['path']=$current_path;
		}
}

function userexists($userID="") {
	global $USERSTABLE;
	if ($userID=="") { //this user
		$count=db_count("SELECT * FROM `$USERSTABLE` WHERE `userID`=${_SESSION['granted']};");
		if ($count>0) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else { //other user
		$count=db_count("SELECT * FROM `$USERSTABLE` WHERE `userID`=$userID;");
		if ($count>0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

function getmyrights() {
	global $USERSTABLE;
	$rights=db_asocquery("SELECT lmr.`rightID` FROM
	`$USERSTABLE` ut
	JOIN `lmuserroles` lur ON
	ut.`userID`=lur.`userID`
	JOIN `lmrolerights` lrr ON
	lur.`roleID`=lrr.`roleID`
	JOIN `lmrights` lmr ON
	lrr.`rightID`=lmr.`rightID`
	WHERE ut.`userID`=${_SESSION['granted']};");
	return $rights;
}

function checkright($rightName) {
	//check if user has a single right
	if (!userexists()) return FALSE;
	global $USERSTABLE,$LM_LEARNING_MODE;
	//when in learning mode, framework will add all checked rights into the lmrights table
	if ($LM_LEARNING_MODE==1) {
		if (db_count("SELECT * FROM `lmrights` WHERE `rightName`='$rightName';")==0) {
			db_uquery("INSERT INTO `lmrights` VALUES(DEFAULT,'$rightName');");
		}
	}
	//end learning
	$count=db_count("SELECT lmr.`rightID` FROM
	`$USERSTABLE` ut
	JOIN `lmuserroles` lur ON
	ut.`userID`=lur.`userID`
	JOIN `lmrolerights` lrr ON
	lur.`roleID`=lrr.`roleID`
	JOIN `lmrights` lmr ON
	lrr.`rightID`=lmr.`rightID`
	WHERE lmr.`rightName`='$rightName'
	AND ut.`userID`=${_SESSION['granted']};");
	if ($count>0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function checkrights($rightNames) { //comma-separated string
	//check if user has at least one of the comma-separated list of rights
	if (!userexists()) return FALSE;
	$tmp="";
	$exploded=explode(',',$rightNames);
	foreach ($exploded as $name) {
		$tmp="$tmp'$name',";
	}
	$tmp=substr_replace($tmp ,"",-1); //remove last comma
	global $USERSTABLE,$LM_LEARNING_MODE;
	//when in learning mode, framework will add all checked rights into the lmrights table
	if ($LM_LEARNING_MODE==1) {
		foreach ($exploded as $right) {
			if (db_count("SELECT * FROM `lmrights` WHERE `rightName`='$right';")==0) {
				db_uquery("INSERT INTO `lmrights` VALUES(DEFAULT,'$right');");
			}
		}
	}
	//end learning
	$sql="SELECT lmr.`rightID` FROM
	`$USERSTABLE` ut
	JOIN `lmuserroles` lur ON
	ut.`userID`=lur.`userID`
	JOIN `lmrolerights` lrr ON
	lur.`roleID`=lrr.`roleID`
	JOIN `lmrights` lmr ON
	lrr.`rightID`=lmr.`rightID`
	WHERE lmr.`rightName` IN ($tmp)
	AND ut.`userID`=${_SESSION['granted']};";
	//echo("DEBUG: $sql");
	$count=db_count($sql);
	if ($count>0) {
		return TRUE;
	} else {
		return FALSE;
	}	
}

function getcss() {
	if (!userexists()) return FALSE;
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {
			$sql="SELECT `css` FROM `$USERSTABLE` WHERE `userID`=${_SESSION['granted']};";
			$result=db_query($sql);
			return $result[0][0];
		} else {
			return FALSE;
		}
}

function getusername() { //obsolete and stupid!!
	//if (!userexists()) return FALSE;
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {
		$sql="SELECT `login` FROM `$USERSTABLE` WHERE `userID`=${_SESSION['granted']};";
		$result=db_query($sql);
		return $result[0][0];
	} else {
		return FALSE;
	}
}

function setprefs($prefs) {
	//prefs is an associative array, contains all preferences
	//prefs=>defaultPage
	//prefs=>css
	if (!userexists()) return FALSE;
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {		
			$sql="UPDATE `$USERSTABLE` SET
			`defaultPage`='${prefs['defaultPage']}',
			`css`='${prefs['css']}'
			WHERE `userID`=${_SESSION['granted']};";
			db_uquery($sql);
	}
}

function getprefs() {
	if (!userexists()) return FALSE;
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {
			$sql="SELECT * FROM `$USERSTABLE` WHERE `userID`=${_SESSION['granted']};";
			$result=db_asocquery($sql);
			return $result[0];
	}
}

function getusers($options='') {
	global $USERSTABLE;
	$sql="SELECT `userID` , `login` , `pass` , `lastip` , STR_TO_DATE( `last` , '%d.%m.%Y %H:%i' ) AS last, `defaultPage` , `css` , `act` FROM $USERSTABLE $options";
	$result=db_asocquery($sql);
	return($result);
}

function updatelast($date,$ip) {
	if (!userexists()) return FALSE;
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {		
			$sql="UPDATE `$USERSTABLE` SET
			last='$date',
			lastip='$ip'
			WHERE `userID`=${_SESSION['granted']};";
			db_uquery($sql);
	}	
}

function hashpassLegacy($pass) { //create a salted hash
	global $LM_SALT;
	return md5($LM_SALT.$pass);
}

function hashpass($pass) { //create a salted hash
	global $LM_SALT;
        $algo='sha256'; $repeats=10000;
        $hash=hash($algo,$LM_SALT.$pass);
        for ($i=0; $i<$repeats; $i++) {
                $hash=hash($algo,$hash);
        }
	return $hash;
}

//function updateUserstable() moved to dbcatalog.php

function checkpass($pass) {
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {
		$hash=hashpass($pass);
		$result=db_query("SELECT `userID` FROM `$USERSTABLE` WHERE `userID`='${_SESSION['granted']}' AND pass='$hash';");
		if (count($result)==1) {
			return TRUE;
		} else {
                    //fallback to md5 password
                    $hash=hashpassLegacy($pass);
                    $result=db_query("SELECT `userID` FROM `$USERSTABLE` WHERE `userID`='${_SESSION['granted']}' AND pass='$hash';");
                    if (count($result)==1) {
                            return TRUE;
                    }
                }
	}
	return FALSE;
}

function setpass($newpass) {
	if (!userexists()) return FALSE;
	global $USERSTABLE;
        updateUserstable();
	$newpass=hashpass($newpass);
	if (isset($_SESSION['granted'])) {
			$sql="UPDATE `$USERSTABLE` SET pass='$newpass' WHERE `userID`=${_SESSION['granted']};";
			db_uquery($sql);
	}
}

/****************************** CZʌ� AUTORYZUJ�CA ******************************/

function auth_user($login,$password) {
	global $USERSTABLE;
	$_SESSION['LOGIN_REALM']='local';
	//LDAP
	if (ldap_auth($login, $password)) {
                //if password is valid in LDAP, we only have to check if user exists in the DB
                $result=db_query("SELECT `userID` FROM `$USERSTABLE` WHERE login='$login' AND act=1;");
                if (count($result)==1) {
                        return($result[0][0]);
                }
        } else {
                //if LDAP didn't work, we check both login and passwd
                $hash=hashpass($password);
                $result=db_query("SELECT `userID` FROM `$USERSTABLE` WHERE login='$login' AND pass='$hash' AND act=1;");
                if (count($result)==1) {
                        return($result[0][0]);
                } else {
                    //fallback to old MD5 password
                    $hash=hashpassLegacy($password);
                    $result=db_query("SELECT `userID` FROM `$USERSTABLE` WHERE login='$login' AND pass='$hash' AND act=1;");
                    if (count($result)==1) {
                        header("Location: index.php?id=5&id2=2&legacy=1");
                        //no die() here because we want the auth process to complete
                        return($result[0][0]);
                    }
                }
        }
	//END LDAP
	return(-1);
}

?>

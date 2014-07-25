<?php
//$granted=-1;
include_once("ldap.php");
include_once("db.php");

function check_includes_for_auth_php() {
	return TRUE;
}

function checksession() {
	if ($_SESSION['status']!=1) die("Wrong script call. <a href=\"index.php\">$LM_APP_NAME</a>");
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
	$sql="SELECT * FROM $USERSTABLE $options";
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

function hashpass($pass) { //create a salted hash
	global $LM_SALT;
	return md5($LM_SALT.$pass);
}

function checkpass($pass) {
	global $USERSTABLE;
	if (isset($_SESSION['granted'])) {
		$password=hashpass($pass);
		$sql="SELECT `userID` FROM `$USERSTABLE` WHERE `userID`='${_SESSION['granted']}' AND pass='$password';";
		$result=db_query($sql);
		$ileadmin=count($result);
		if ($ileadmin==1) {
			return TRUE;
		}
	}
	return FALSE;
}

function setpass($newpass) {
	if (!userexists()) return FALSE;
	global $USERSTABLE;
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
                $sql="SELECT `userID` FROM `$USERSTABLE` WHERE login='$login' AND act=1;";
        } else {
                //if LDAP didn't work, we check both login and passwd
                $password=hashpass($password);
                $sql="SELECT `userID` FROM `$USERSTABLE` WHERE login='$login' AND pass='$password' AND act=1;";
        }
	//END LDAP
	$result=db_query($sql);
	$ileadmin=count($result);
	if ($ileadmin==1) {
		return($result[0][0]);
	}
	return(-1);
}

?>

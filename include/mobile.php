<?php

function isMobileUserAgent() {
    $agents=array( '/iPhone|Android.*[mM]obile|BlackBerry|IEMobile|Windows Phone|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/' );
    foreach ($agents as $agent) {
        if (preg_match($agent,$_SERVER['HTTP_USER_AGENT'])) return TRUE;
    }
    return FALSE;
}

function mobile_template_main() {
	global $LM_APP_NAME, $lmver, $LANG, $LM_READONLY;
	?>
	<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php" />
	<title><?php echo("$LM_APP_NAME $lmver"); ?></title>
	<?php
	applycss('css/mobile.css');
	?>
        <!--<link rel="stylesheet" href="jquery-ui/css/ui-darkness/jquery-ui-1.10.3.custom.min.css" />-->
	<link rel="icon" href="favicon.ico" type="image/ico" />
        <script type="text/javascript" src="<?=getUrl()?>jquery-ui/js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>jquery-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>chart.js/Chart.min.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>ajax.js"></script>
        <script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
	<script type="text/javascript">
		function logoff() {
		logout=window.open("index.php?logoff=1","logoff_window","toolbar=0,location=0,scrollbars=0,resizable=0,height=64,width=64,top=100,left=100");
		logout.close();
		}
            </script>
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
        <div class="tab-horizbar">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td width="50%" align="left"><div class="top">Logged in as:<b> <?php
			echo(getusername());
		?></b><br/></div></td>
		<td width="50%"><div class="top2"><a href="?id=5"><img src="<?=getUrl()?>img/settings.gif" alt="Settings" style="vertical-align: middle;"/></a><a href="index.php?logoff=1"><img src="<?=getUrl()?>img/log.gif" alt="Log off" style="vertical-align: middle;"/> <b>Log off</b></a>
		<br/></div></td></tr>
		</table>
        </div>

	<img src="<?=getUrl()?>img/LMeve.png" alt="Logo" />
	<?php //draw messages notify
	include("msgchk.php");
	include("custom_notifications.php");
	if ($LM_READONLY==1) echo($LANG['READONLY']);
	?>

        <div class="tab-horizbar" style="text-align: center;">
            <select id="menu" onchange="haha('menu');">

            <?php //draw menu
            if (!isset($_GET['id'])) {
                    $id=getprefs();
                    $id=$id['defaultPage'];
            } else {
                    $id=$_GET['id'];
            }

            menu($id);
            ?>
            </select>
        </div>
           

		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td width="100%" class="tab-main" id="tab-main" valign="top">
			<?php
				showTabContents($id);
			?>
		</td>
		</tr>
		</table>
	
        <div class="tab-horizbar" style="text-align: center;">
	<a href="index.php?id=254">About</a><br/>
	</div>
	<?php
	include("copyright.php");
	?>
	</center>
	</body>
	</html><?php
}

function mobile_template_locked($msg=null) {
	global $LM_APP_NAME,$LM_DEFAULT_CSS,$LANG;
        if (is_null($msg)) $msg=$LANG['MAINTENANCE'];
	?>
	<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo($LM_APP_NAME); ?> - Unavailable</title>
	<?php
	applycss('css/mobile.css');
	?>
	<link rel="icon" href="favicon.png" type="image/png" />
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
	<br />
	<table border="0" cellspacing="0" cellpadding="0" width="90%" class="login">
	
	
	<tr><td><br><div class="tcen"><?php echo($msg); ?>
	</div></td></tr>
	<tr><td><div class="tcen"><br>
		 <form method="get" action="">
			<input type="submit" value="Try again">
		 </form>
		 </div>
	</td></tr>
	</table>
	<?php
	include("copyright.php");
	?>
	</center>
	</body>
	</html>
	<?php
}

function mobile_template_login() {
	global $LM_APP_NAME,$LM_DEFAULT_CSS,$LANG,$SSOENABLED;
	?>
	<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo($LM_APP_NAME); ?> - Please log in</title>
	<?php
	applycss('css/mobile.css');
	?>
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
	<br>
	<form name="loginform" id="loginform" method="post" action="">
	
	<table border="0" cellspacing="0" cellpadding="0" width="90%" class="login">
	
	
	<tr><td align="left">&nbsp;<!--<br><label for="user_login">User:</label>--></td></tr>
	<tr><td><div class="tcen">
			<input name="login" placeholder="Login" id="user_login" size=20 type="text" value="" style="" autocapitalize="off">
		</div>
	</td></tr>
	<tr><td align="left">&nbsp;<!--<label for="user_pass">Password:</label>--></td></tr>
	<tr><td><div class="tcen">
                        <input name="password" placeholder="Password" id="user_pass" size="20" type="password" style="" autocapitalize="off">
		</div>
	</td></tr>
        <tr><td align="left">&nbsp;<!--<label for="user_pass">Password:</label>--></td></tr>
	<tr><td><div class="tcen">
			<input name="logon" type="submit" value="Log in"><br/>
			<?php if ($SSOENABLED) { ?> <hr style="opacity: 0.2;" />
			<a href="ssologin.php"><img src="<?=getUrl()?>img/EVE_SSO_Login_Buttons_Small_White.png"></a>
			<?php } ?>
		 </div>
	</td></tr>
	</table>
	<?php
	include('copyright.php');
	?>
	</form>
	</center>
	</body>
	</html>
	<?php
}

function mobile_template_badlogon() {
	global $LM_APP_NAME,$LM_DEFAULT_CSS,$LANG;	
	?>
	<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo($LM_APP_NAME); ?> - Please log in</title>
	<?php
	applycss('css/mobile.css');
	?>
	<link rel="icon" href="favicon.ico" type="image/ico">
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
	<br>
	<table border="0" cellspacing="0" cellpadding="0" width="90%" class="login">
	<tr><td><div class="tcen"><br>Wrong username<br>or password.<br>&nbsp;</div></td></tr>
	<tr><td align="center"><div class="tcen"><br>
		 <form method="get" action="">
			<input type="submit" value="Back">
		 </form></div>
	</td></tr>
	</table>
	</center>
	<?php
	 include('copyright.php');
	?>
	</body>
	</html>
	<?php
}

function mobile_template_logout($msg='Logged out.') {
	global $LM_APP_NAME,$LM_DEFAULT_CSS,$LANG;
	?>
	<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<META HTTP-EQUIV="Refresh" CONTENT="3; URL=index.php" />
	<title><?php echo($LM_APP_NAME); ?> - Logged out</title>
	<?php
	applycss('css/mobile.css');
	?>
	<link rel="icon" href="favicon.ico" type="image/ico">
	</head>
	<body text="#000000" bgcolor="#FFFFFF">
	<center>
	<br>
	<table border="0" cellspacing="0" cellpadding="0" width="90%" class="login">
	
	
	<tr><td><br><div class="tcen"><?=$msg?></div></td></tr>
	<tr><td><div class="tcen"><br>
		 <form method="get" action="">
			<input type="submit" value="Login again">
		 </form>
		 </div>
	</td></tr>
	</table>
	<?php
	include("copyright.php");
	?>
	</center>
	</body>
	</html>
	<?php
}
?>

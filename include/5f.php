<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,UseNorthboundApi")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='LMeve Northbound API keys'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;
include_once('csrf.php');

if (!token_verify()) die("Invalid or expired token.");
?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>
Creating key...
<?php
    $token = substr(preg_replace('/[0-9_\/]+/','',base64_encode(sha1(random_pseudo_bytes_wrapper(64)))),0,32);
    $sql="INSERT INTO `lmnbapi` VALUES(DEFAULT,${_SESSION['granted']},'$token',DEFAULT,DEFAULT);";
    db_uquery($sql);
?>
<input type="button" value="OK" onclick="location.href='?id=5&id2=14';"/>
<script type="text/javascript">location.href="index.php?id=5&id2=14";</script>


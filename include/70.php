<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewUsers")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=7; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Users'; //Panel name (optional)
//standard header ends here

 	include('../config/config.php'); //wczytaj nastawy konfiguracji
?>

            <table cellpadding="0" cellspacing="2">
	    <tr><td>
	    <?php if (checkrights("Administrator,EditUsers")) { ?>
	    <form method="get" action="">
		    <input type="hidden" name="id" value="7">
		    <input type="hidden" name="id2" value="2">
		    <input type="hidden" name="nr" value="new">
		    <input type="submit" value="Add user">
		</form>
		<?php } ?>
		</td><td>
		<?php if (checkrights("Administrator,EditRoles")) { ?>
	    <form method="get" action="">
		    <input type="hidden" name="id" value="7">
		    <input type="hidden" name="id2" value="1">
		    <input type="submit" value="Edit roles">
		</form>
		<?php } ?>
		</td></tr></table>

            <div class="tytul">
		Users<br>
	    </div>

	    
	    
	    
	<?php
function gethost($ip)
{
   $host = `host $ip`;
   $host=end(explode(' ',$host));
   $host=substr($host,0,strlen($host)-2);
   $chk=split("\(",$host);
   if($chk[1]) return $ip." (".$chk[1].")";
   else return $host;
}
        include("users.php");
	
 	$admini=getusers('WHERE `act`=1 ORDER BY `login`');
        $inactive=getusers('WHERE `act`!=1 ORDER BY `login`');
        ?>
<script type="text/javascript" src="jquery-tablesorter/jquery.tablesorter.min.js"></script>
<link rel="stylesheet" type="text/css" href="jquery-tablesorter/blue/style.css">
  
        <table class="lmframework" width="422">
	    <tr><th width="100%">
	    <img src="<?=getUrl()?>ccp_icons/38_16_170.png" alt="[+]" style="vertical-align: middle;" /> Active users
	    </th></tr>
        </table>
        <?php
        showUsers($admini);
        ?>
        <table class="lmframework" width="422">
	    <tr><th width="100%">
	    <img src="<?=getUrl()?>ccp_icons/38_16_169.png" alt="[x]" style="vertical-align: middle;" /> Disabled users
	    </th></tr>
        </table>
        <?php 
        showUsers($inactive);
            /*
	    <form action="" method="get">
	    <input type="hidden" name="id" value="7">
	    <input type="hidden" name="id2" value="1">
	    <input type="submit" value="Clear the list of recently logged users">		
	    </form>
	    */ ?>
	    <table cellspacing="2" cellpadding="0" border="0">
	    <tr><td class="tab-header">
	    <img src="<?=getUrl()?>img/info.gif" alt="i"><b>Legend:</b><br>
	    </td></tr><tr><td>
		<table cellspacing="0" cellpadding="0" border="0">
		<tr><td width="50" class="tab-act"><br></td><td>- online user<br></td>
		<td width="50" class="tab"><br></td><td>- offline user<br></td>
		</tr>
		</table>
	    </td></tr><tr><td class="tab"><img src="<?=getUrl()?>img/msg.gif" alt="MSG"> - Send message<br></td>
	    </tr></table>
	<script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
	<script type="text/javascript">
		reloader("?id=7",20);
	</script>

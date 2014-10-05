<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}*/
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

include('../config/config.php'); //wczytaj nastawy konfiguracji

global $LM_EVEDB;
?>

<div class="tytul">
Settings
</div>

<h2>Server Statistics</h2>
<b>Server time:</b>
<?php
	echo('<a href="#" title="');
	echo(date('d.m.Y G:i:s'));
	echo('">');
	echo(date('d.m.Y G:i'));
	echo('</a>');

?>
<br/>
<b>Login realm:</b> <?php 
    switch ($_SESSION['LOGIN_REALM']) {
        case 'local':
            echo('LMeve internal database');
            break;
        case 'LDAP':
            echo('External LDAP server');
            break;
        case 'EVE_SSO':
            echo('EVE Online Single Sign-On');
            break;
        default:
            echo($_SESSION['LOGIN_REALM']);
    }
?><br/>
<b>Server uptime: </b>
<?php
	$uptime=exec('uptime');
	$up2=explode('up',$uptime);
	$tmp=explode(',',$up2[1]);
	echo('<a href="#" title="');
	echo($uptime);
	echo('">');
	echo($tmp[0]);
	echo('</a><br>');

	echo('<b>Maximum session idle time:</b> ');
	if ($LM_SESSION < 60) {
		printf("%d seconds<br/>",$LM_SESSION);
	} else {
		printf("%d minutes<br/>",$LM_SESSION/60);
	}
        
	include("checkpoller.php");  
        
        if (checkrights("Administrator,ViewAPIStats")) { ?>
            <h3>EVE API Statistics have been moved to "Statistics" panel</h3>
            <form action="" method="get">
            <input type="hidden" name="id" value="8" />
            <input type="hidden" name="id2" value="4" />
            <input type="submit" value="EVE API Statistics" />
            </form><?php
        }
        
        if (checkrights("Administrator")) { ?>
            <h3>LMeve Global Settings</h3>
            <form method="post" action="?id=5&id2=13">
                <?php token_generate(); ?>
		<input type="hidden" name="nr" value="<?=$nr?>">
		    <table border="0" cellspacing="2" cellpadding="">
                        <tr><td width="150" class="tab">
                            ISK per point:<br></td><td width="200" class="tab">
                                <input type="text" size="32" name="iskPerPoint" value="<?=getConfigItem('iskPerPoint','15000000')?>" />
                        </td></tr>
                        <tr><td width="150" class="tab">
                            Enable Northbound API:<br></td><td width="200" class="tab">
                                <input type="checkbox" size="32" name="northboundApi" <?php if (getConfigItem('northboundApi','disabled')=='enabled') echo('checked'); ?> />
                        </td></tr>
                        <tr><td width="150" class="tab">
                            Get market prices from:<br></td><td width="200" class="tab">
                                <select name="marketRegion"> <?php
                                $regions=db_asocquery("SELECT `regionID`,`regionName` FROM `$LM_EVEDB`.`mapRegions` ORDER BY `regionName`;");
                                $currentRegion=getConfigItem('marketRegion', '10000002');
                                foreach($regions as $row) {
                                            if ($row['regionID']==$currentRegion) $select='selected'; else $select='';
                                            echo("<option value=\"${row['regionID']}\" $select>${row['regionName']}</option>");
                                }
                                ?></select>
                        </td></tr>
		</table>
                <br/>
                <input type="submit" value="Save configuration"/><br/>
		
            </form>
            <?php
        }
?>

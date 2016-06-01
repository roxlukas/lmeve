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
		    <table class="lmframework">
                        <tr>
                            <td width="300">
                                ISK per point:<br/>
                            </td>
                            <td width="300">
                                    <input type="text" size="32" name="iskPerPoint" value="<?=getConfigItem('iskPerPoint','15000000')?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Enable LMeve Northbound API:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="northboundApi" <?php if (getConfigItem('northboundApi','disabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Enable public Killboard:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="publicKillboard" <?php if (getConfigItem('publicKillboard','disabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Use hybrid XML API / CREST killmail fetching:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="useCRESTkillmails" <?php if (getConfigItem('useCRESTkillmails','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Use CCP WebGL in Killboard and Inventory<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="useWebGLpreview" <?php if (getConfigItem('useWebGLpreview','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Non-recurring tasks expiration:<br/>
                            </td>
                            <td>
                                <input type="text" size="6" name="singletonTaskExpiration" value="<?=getConfigItem('singletonTaskExpiration','90');?>" /> days
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Relic to use for Tech III:<br/>
                            </td><td>
                                <select name="T3relicType"> <?php
                                $relics=array("Intact","Malfunctioning","Wrecked");
                                $currentRelic=getConfigItem('T3relicType','Wrecked');
                                foreach($relics as $row) {
                                            if ($row==$currentRelic) $select='selected'; else $select='';
                                            echo("<option value=\"$row\" $select>$row</option>");
                                }
                                ?></select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Buy calculator price multiplier:<br/>
                                <em>1.0 = original price</em>
                            </td>
                            <td>
                                <input type="text" size="6" name="buyCalcPriceModifier" value="<?=getConfigItem('buyCalcPriceModifier', 1.0);?>" />x
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Silo notification threshold:<br/>
                            </td>
                            <td>
                                <input type="text" size="6" name="siloPercentage" value="<?=getConfigItem('siloPercentage', 90);?>" />%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Get market prices from:<br/>
                            </td>
                            <td>
                                <select name="marketRegion" id="marketRegion" onchange="systemSelect('marketSystemID',this.value);"></select>
                                <select name="marketSystemID" id="marketSystemID">
                                    <?php
                                        $currentSystem=getConfigItem('marketSystemID', '30000142');
                                        $systems=db_asocquery("SELECT `solarSystemName` FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `solarSystemID`=$currentSystem;");
                                        $row=$systems[0];
                                        echo("<option value=\"${row['solarSystemID']}\" selected>${row['solarSystemName']}</option>");
                                    ?>
                                </select>
                                <script type="text/javascript">
                                    regionSelect('marketRegion');
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Get industry indices from:<br/>
                            </td>
                            <td>
                                <select name="indexRegion" id="indexRegion" onchange="systemSelect('indexSystemID',this.value);"></select>
                                <select name="indexSystemID" id="indexSystemID">
                                    <?php
                                        $currentSystem=getConfigItem('indexSystemID', '30000142');
                                        $systems=db_asocquery("SELECT `solarSystemName` FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `solarSystemID`=$currentSystem;");
                                        $row=$systems[0];
                                        echo("<option value=\"${row['solarSystemID']}\" selected>${row['solarSystemName']}</option>");
                                    ?>
                                </select>
                                <script type="text/javascript">
                                    regionSelect('indexRegion');
                                </script>
                            </td>
                        </tr>
                    </table>
                <br/>
                <input type="submit" value="Save configuration"/><br/>
		
            </form>
            <?php
        }
?>

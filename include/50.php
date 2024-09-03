<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
/*if (!checkrights("Administrator,ViewTimesheet")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>{$LANG['NORIGHTS']}</h2>");
	return;
}*/
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Settings'; //Panel name (optional)
//standard header ends here

include('../config/config.php'); //wczytaj nastawy konfiguracji
include_once('market.php');

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
                <?php 
                
                /****** THIS FORM IS SAVED BY 5d.php ******/
                
                token_generate(); 
                
                ?>
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
                                Enable ESI Poller:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="useESI" <?php if (getConfigItem('useESI','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                ESI DEBUG logging (very verbose; up to 1GB logs per day):<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="ESIdebug" <?php if (getConfigItem('ESIdebug','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                ESI datasource:<br/>
                            </td><td>
                                <select name="ESIdatasource"> <?php
                                $relics=array("tranquility","singularity");
                                $currentSource=getConfigItem('ESIdatasource','tranquility');
                                foreach($relics as $row) {
                                            if ($row==$currentSource) $select='selected'; else $select='';
                                            echo("<option value=\"$row\" $select>$row</option>");
                                }
                                ?></select>
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
                                Auto hide Non-recurring tasks:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="singletonTaskAutoHide" <?php if (getConfigItem('singletonTaskAutoHide','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only allow to assign tasks to characters linked to a user:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="only_linked_chars" <?php if (getConfigItem('only_linked_chars','enabled')=='disabled') echo('checked'); ?> />
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
                                Buy calculator price multiplier (low stock): <img src="<?=getUrl()?><?php echo($LM_HINTGREENIMG); ?>" style="display: inline; vertical-align:bottom;  margin: 0 5px;" title="<?php echo($LM_HINTGREEN); ?>" /><br/>
                                <em>1.0 = original price</em>
                            </td>
                            <td>
                                <input type="text" size="6" name="buyCalcPriceModifier" value="<?=getConfigItem('buyCalcPriceModifier', 1.0);?>" />x
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Buy calculator price multiplier (high stock): <img src="<?=getUrl()?><?php echo($LM_HINTYELLOWIMG); ?>" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="<?php echo($LM_HINTYELLOW); ?>" /><br/>
                                <em>1.0 = original price</em>
                            </td>
                            <td>
                                <input type="text" size="6" name="buyCalcPriceModifierHigh" value="<?=getConfigItem('buyCalcPriceModifierHigh', 0.9);?>" />x
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Buy calculator price multiplier (very high stock): <img src="<?=getUrl()?><?php echo($LM_HINTREDIMG); ?>" style="display: inline; vertical-align:bottom; margin: 0 5px;" title="<?php echo($LM_HINTRED); ?>" /><br/>
                                <em>1.0 = original price</em>
                            </td>
                            <td>
                                <input type="text" size="6" name="buyCalcPriceModifierVeryHigh" value="<?=getConfigItem('buyCalcPriceModifierVeryHigh', 0.8);?>" />x
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                Calculate Ore prices based on composition instead of fetching from ESI:<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="calculate_ore_prices" <?php if (getConfigItem('calculate_ore_prices','disabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Reprocessing yield<br/>
                                <em>Used for Ore Chart and Ore prices based on mineral composition. 0.5 - 1.0</em>
                            </td>
                            <td>
                                <input type="text" size="6" name="reprocessingYield" value="<?=getConfigItem('reprocessingYield', 0.6957);?>" />x
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Use <em>Item Groups</em> instead of <em>Market Groups</em> in Database<br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="item_group_explorer" <?php if (getConfigItem('item_group_explorer','disabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Get market prices from:<br/>
                            </td>
                            <td>
                                <select name="marketRegionID" id="marketRegionID" onchange="systemSelect('marketSystemID',this.value);"></select>
                                <select name="marketSystemID" id="marketSystemID">
                                    <?php
                                        $currentSystem=getConfigItem('marketSystemID', '30000142');
                                        $systems=db_asocquery("SELECT `solarSystemName` FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `solarSystemID`=$currentSystem;");
                                        $row=$systems[0];
                                        echo("<option value=\"{$row['solarSystemID']}\" selected>{$row['solarSystemName']}</option>");
                                    ?>
                                </select>
                                <script type="text/javascript">
                                    regionSelect('marketRegionID',<?=getConfigItem('marketRegionID','10000002')?>);
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Get industry indices from:<br/>
                            </td>
                            <td>
                                <select name="indexRegionID" id="indexRegionID" onchange="systemSelect('indexSystemID',this.value);"></select>
                                <select name="indexSystemID" id="indexSystemID">
                                    <?php
                                        $currentSystem=getConfigItem('indexSystemID', '30000142');
                                        $systems=db_asocquery("SELECT `solarSystemName` FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `solarSystemID`=$currentSystem;");
                                        $row=$systems[0];
                                        echo("<option value=\"{$row['solarSystemID']}\" selected>{$row['solarSystemName']}</option>");
                                    ?>
                                </select>
                                <script type="text/javascript">
                                    regionSelect('indexRegionID',<?=getConfigItem('indexRegionID','10000002')?>);
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Enable sending LMeve usage stats:<br/>
                                <em>LMeve will anonymously send once a week the amount of active users in last 7d and 30d</em>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="usageStats" <?php if (getConfigItem('usageStats','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <!--
                        <tr>
                            <td>
                                <em>Use hybrid XML API / CREST killmail fetching (DEPRECATED):</em><br/>
                            </td>
                            <td>
                                <input type="checkbox" size="32" name="useCRESTkillmails" <?php if (getConfigItem('useCRESTkillmails','enabled')=='enabled') echo('checked'); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <em>Silo notification threshold (DEPRECATED):</em><br/>
                            </td>
                            <td>
                                <input type="text" size="6" name="siloPercentage" value="<?=getConfigItem('siloPercentage', 90);?>" />%
                            </td>
                        </tr> -->
                    </table>
                <br/>
                <input type="submit" value="Save configuration"/><br/>
		
            </form>
            <?php
        }
?>

<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>{$LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Save settings'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

?>		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    
<?php

                if (!token_verify()) die("Invalid or expired token.");
                setConfigItem('iskPerPoint', secureGETnum('iskPerPoint'));
                setConfigItem('singletonTaskExpiration', secureGETnum('singletonTaskExpiration'));
                $buyCalcPriceModifier=secureGETstr('buyCalcPriceModifier');
                if (!is_numeric($buyCalcPriceModifier)) die('Wrong parameter value buyCalcPriceModifier: must be numeric'); else setConfigItem('buyCalcPriceModifier', $buyCalcPriceModifier);
                $buyCalcPriceModifierHigh=secureGETstr('buyCalcPriceModifierHigh');
                if (!is_numeric($buyCalcPriceModifierHigh)) die('Wrong parameter value buyCalcPriceModifier: must be numeric'); else setConfigItem('buyCalcPriceModifierHigh', $buyCalcPriceModifierHigh);
                $buyCalcPriceModifierVeryHigh=secureGETstr('buyCalcPriceModifierVeryHigh');
                if (!is_numeric($buyCalcPriceModifierVeryHigh)) die('Wrong parameter value buyCalcPriceModifier: must be numeric'); else setConfigItem('buyCalcPriceModifierVeryHigh', $buyCalcPriceModifierVeryHigh);
                $marketSystemID=secureGETnum('marketSystemID');
                if (!empty($marketSystemID)) setConfigItem('marketSystemID', $marketSystemID);
                $marketRegionID=secureGETnum('marketRegionID');
                if (!empty($marketRegionID)) setConfigItem('marketRegionID', $marketRegionID);
                $indexSystemID=secureGETnum('indexSystemID');
                if (!empty($indexSystemID)) setConfigItem('indexSystemID', $indexSystemID);
                $indexRegionID=secureGETnum('indexRegionID');
                if (!empty($indexRegionID)) setConfigItem('indexRegionID', $indexRegionID);
                
                $reprocessingYield = secureGETstr('reprocessingYield');
                if (!is_numeric($reprocessingYield)) die('Wrong parameter value reprocessingYield: must be numeric'); else {
                    if ($reprocessingYield < 0.5) $reprocessingYield = 0.5;
                    if ($reprocessingYield > 1.0) $reprocessingYield = 1.0;
                    setConfigItem('reprocessingYield', $reprocessingYield);
                }
                
                if (secureGETstr('northboundApi')=='on') setConfigItem('northboundApi','enabled'); else setConfigItem('northboundApi','disabled');
                if (secureGETstr('useESI')=='on') setConfigItem('useESI','enabled'); else setConfigItem('useESI','disabled');
                if (secureGETstr('ESIdebug')=='on') setConfigItem('ESIdebug','enabled'); else setConfigItem('ESIdebug','disabled');
                if (in_array(secureGETstr('ESIdatasource'),array("tranquility","singularity"))) setConfigItem('ESIdatasource', secureGETstr('ESIdatasource'));
                if (secureGETstr('publicKillboard')=='on') setConfigItem('publicKillboard','enabled'); else setConfigItem('publicKillboard','disabled');
                if (secureGETstr('useCRESTkillmails')=='on') setConfigItem('useCRESTkillmails','enabled'); else setConfigItem('useCRESTkillmails','disabled');
                if (secureGETstr('useWebGLpreview')=='on') setConfigItem('useWebGLpreview','enabled'); else setConfigItem('useWebGLpreview','disabled');
                if (secureGETstr('singletonTaskAutoHide')=='on') setConfigItem('singletonTaskAutoHide','enabled'); else setConfigItem('singletonTaskAutoHide','disabled');
                if (secureGETstr('only_linked_chars')=='on') setConfigItem('only_linked_chars','enabled'); else setConfigItem('only_linked_chars','disabled');
                if (secureGETstr('calculate_ore_prices')=='on') setConfigItem('calculate_ore_prices','enabled'); else setConfigItem('calculate_ore_prices','disabled');
                if (secureGETstr('item_group_explorer')=='on') setConfigItem('item_group_explorer','enabled'); else setConfigItem('item_group_explorer','disabled');
                if (secureGETstr('usageStats')=='on') setConfigItem('usageStats','enabled'); else setConfigItem('usageStats','disabled');
                
                if (in_array(secureGETstr('T3relicType'),array("Intact","Malfunctioning","Wrecked"))) setConfigItem('T3relicType', secureGETstr('T3relicType'));
		if (secureGETnum('siloPercentage')>=0 && secureGETnum('siloPercentage')<=100) setConfigItem('siloPercentage', secureGETnum('siloPercentage'));
		?>
		<br>
                <input type="button" value="OK" onclick="location.href='?id=5&id2=0';">
		<script type="text/javascript">jsRedirect("index.php?id=5&id2=0");</script>
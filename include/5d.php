<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
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
                $marketSystemID=secureGETnum('marketSystemID');
                if (!empty($marketSystemID)) setConfigItem('marketSystemID', $marketSystemID);
                $indexSystemID=secureGETnum('indexSystemID');
                if (!empty($indexSystemID)) setConfigItem('indexSystemID', $indexSystemID);
                
                if (secureGETstr('northboundApi')=='on') setConfigItem('northboundApi','enabled'); else setConfigItem('northboundApi','disabled');
                if (secureGETstr('publicKillboard')=='on') setConfigItem('publicKillboard','enabled'); else setConfigItem('publicKillboard','disabled');
                if (secureGETstr('useCRESTkillmails')=='on') setConfigItem('useCRESTkillmails','enabled'); else setConfigItem('useCRESTkillmails','disabled');
                if (secureGETstr('useWebGLpreview')=='on') setConfigItem('useWebGLpreview','enabled'); else setConfigItem('useWebGLpreview','disabled');
                
                if (in_array(secureGETstr('T3relicType'),array("Intact","Malfunctioning","Wrecked"))) setConfigItem('T3relicType', secureGETstr('T3relicType'));
		if (secureGETnum('siloPercentage')>=0 && secureGETnum('siloPercentage')<=100) setConfigItem('siloPercentage', secureGETnum('siloPercentage'));
		?>
		<br>
                <input type="button" value="OK" onclick="location.href='?id=5&id2=0';">
		<script type="text/javascript">location.href="index.php?id=5&id2=0";</script>
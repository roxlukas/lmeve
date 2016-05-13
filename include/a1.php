<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewDatabase")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Item Database'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB, $LM_CCPWGL_URL, $LM_CCPWGL_USEPROXY, $MOBILE, $USERSTABLE, $CREST_BASEURL;
include_once('materials.php');
include_once('yaml_graphics.php');
include_once('skins.php');

$nr=secureGETnum('nr');

function hrefedit_item($nr) {
		echo("<a href=\"index.php?id=10&id2=1&nr=$nr\">");
	}
        
function url_replace($input) {
    return "<a href=\"index.php?id=10&id2=1&nr=".$input[1]."\">";
}

?>	    
		<script type="text/javascript" src="<?=getUrl()?>ajax.js"></script>
		<script type="text/javascript" src="<?=getUrl()?>skin-icon.js"></script>
		<div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php
		if (empty($nr)) {
			echo('Wrong parameter nr.');
			return;
		}
		$item=db_asocquery("SELECT itp.*,igp.`categoryID`
		FROM $LM_EVEDB.`invTypes` itp
        JOIN $LM_EVEDB.`invGroups` igp
        ON itp.`groupID`=igp.`groupID`
		WHERE `typeID` = $nr ;");
		
		if (count($item)==0) {
			echo('There is no such record in the database.');
			return;
		}
		
		$item=$item[0];
		
		?>

		<table cellpadding="0" cellspacing="2" width="95%">
	    <tr>
	    
	    <td style="width: 20%; min-width: 182px;">
		<form method="get" action="">
		<input type="text" name="query" value="" size="20" style="width: 120px;">
		<input type="hidden" name="id" value="10">
		<input type="hidden" name="id2" value="2">
	    <input type="submit" value="Search">
		</form>
		</td>
		<?php
                if ($MOBILE) echo('</tr><tr>');
//TASKS INTEGRATION
		if (checkrights("Administrator,EditTasks")) {
			echo('<td  style="width: 35%; padding: 5px 0px 5px 0px;">');
			$tasks=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, ibt.`blueprintTypeID`, iit.`productTypeID`, ibt.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel
			FROM $LM_EVEDB.`invTypes` itp
			LEFT JOIN $LM_EVEDB.`invBlueprintTypes` ibt
			ON itp.typeID=ibt.blueprintTypeID
			LEFT JOIN $LM_EVEDB.`invBlueprintTypes` iit
			ON itp.typeID=iit.productTypeID
			WHERE `typeID`=$nr
			AND ( (ibt.`blueprintTypeID` IS NOT NULL) OR (iit.`productTypeID` IS NOT NULL) )
			AND itp.`published`=1;");
			if (count($tasks)==1) {
				$tasks=$tasks[0];
				echo('<strong>Add task:</strong> ');
				if (!empty($tasks['productTypeID'])) {
					echo('<input type="button" value="Manufacturing" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=1\';">');
					$isBlueprint=FALSE;
				}
				if (!empty($tasks['blueprintTypeID'])) {
					if ($tasks['bpoTechLevel']==2 || $tasks['bpoTechLevel']==3) echo('<input type="button" value="Invention" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=8\';">');
					echo('<input type="button" value="Copying" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=5\';">');
					echo('<input type="button" value="ME" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=4\';">');
					echo('<input type="button" value="PE" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=3\';">');
					$isBlueprint=TRUE;
				}
			}
			echo('</td>');
		}
        if ($MOBILE) echo('</tr><tr>');
                
		if (!is_null($item['marketGroupID'])) {
			$pricesDisabled='';
		} else {
			$pricesDisabled='disabled';
		}
                
                echo('<td>');
                echo('<table style="width: 100%;"><tr>');
                
		if (checkrights("Administrator,EditPricesFlag")) {
                        if (!$MOBILE) {
                            echo('<td style="width: 12%; min-width: 110px;">');
                        } else {
                            echo('<td>');
                        }
			echo('<strong>Fetch Prices: </strong>');
			$pricesFlag=db_asocquery("SELECT * FROM `cfgmarket` WHERE `typeID`=$nr;");
			if (count($pricesFlag)>0) {
				$pricesChecked='checked';
			} else {
				$pricesChecked='';
			}
			echo('<input type="checkbox" name="cfgmarket" id="cfgmarket" '.$pricesChecked.' '.$pricesDisabled.' onclick="ajax_save(\'index.php?id=10&id2=3&nr='.$nr.'\',\'cfgmarket\',\'cfgmarket_label\');">');
			echo('<div id="cfgmarket_label" style="position: fixed;"></div>');
			echo('</td>');
		}
		if (checkrights("Administrator,EditBuyingFlag")) {
                        if (!$MOBILE) {
                            echo('<td style="width: 13%; min-width: 130px;">');
                        } else {
                            echo('<td>');
                        }
			echo('<strong>Show in Buy Calc: </strong>');
			$buyingFlag=db_asocquery("SELECT * FROM `cfgbuying` WHERE `typeID`=$nr;");
			if (count($buyingFlag)>0) {
				$buyingChecked='checked';
			} else {
				$buyingChecked='';
			}
			echo('<input type="checkbox" name="cfgbuying" id="cfgbuying" '.$buyingChecked.' '.$pricesDisabled.' onclick="ajax_save(\'index.php?id=10&id2=4&nr='.$nr.'\',\'cfgbuying\',\'cfgbuying_label\');">');
			echo('<div id="cfgbuying_label" style="position: fixed;"></div>');
			echo('</td>');
		}
                if (checkrights("Administrator,EditStock")) {
                        if (!$MOBILE) {
                            echo('<td style="width: 20%; min-width: 180px;">');
                        } else {
                            echo('<td>');
                        }
			$stocks=db_asocquery("SELECT * FROM `cfgstock` WHERE `typeID`=$nr;");
			if (count($stocks)>0) {
				$stockChecked='checked';
                                $stockAmount=$stocks[0]['amount'];
			} else {
				$stockChecked='';
                                $stockAmount=0;
			}
                        echo('<strong>Track: ');
                        echo('<input type="checkbox" name="cfgstock" title="Check \'Track\' first, then input a number in \'Stock\' and press enter" id="cfgstock" '.$stockChecked.' '.$pricesDisabled.' onclick="ajax_save(\'index.php?id=10&id2=6&nr='.$nr.'&amount=\'+getStockAmount(\'stockamount\'),\'cfgstock\',\'cfgstock_label\');">');
                        if ($MOBILE) echo('<br/>');
                        echo(' Stock: <input '.$pricesDisabled.' type="text" name="stockamount" id="stockamount" size="8" title="Press Enter to save the amount" value="'.$stockAmount.'" onchange="ajax_save(\'index.php?id=10&id2=6&nr='.$nr.'&update=1&amount=\'+getStockAmount(\'stockamount\'),\'cfgstock\',\'cfgstock_label\');" /></strong>');
			echo('<div id="cfgstock_label" style="position: fixed;"></div>');
			echo('</td>');
		}
                
                echo('</tr></table>');
                echo('</td>');
                
		?>
		
		</tr></table>

		<?php
                
//PREV-NEXT NAVIGATION
        
        $previous=db_asocquery("SELECT `typeID` FROM $LM_EVEDB.`invTypes` WHERE `typeID` < $nr ORDER BY `typeID` DESC LIMIT 1;"); 
        if (count($previous)>0) $previous_id=$previous[0]['typeID']; else $previous_id=$nr;
        $next=db_asocquery("SELECT `typeID` FROM $LM_EVEDB.`invTypes` WHERE `typeID` > $nr ORDER BY `typeID` ASC LIMIT 1;");
        if (count($next)>0) $next_id=$next[0]['typeID']; else $next_id=$nr;
        ?> <input type="button" value="&laquo;" title="Previous typeID" onclick="location.href='?id=10&id2=1&nr=<?=$previous_id?>';"/>
        <input type="button" value="&raquo;" title="Next typeID" onclick="location.href='?id=10&id2=1&nr=<?=$next_id?>';"/> <?php	

//BREADCRUMB NAVIGATION
	function getMarketNode($marketGroupID) {
		global $LM_EVEDB;
		if (empty($marketGroupID)) return;
		$data=db_asocquery("SELECT * FROM $LM_EVEDB.`invMarketGroups` WHERE `marketGroupID` = $marketGroupID ;");
		if (sizeof($data)==1) return($data[0]); else return;
	}
	
	$blueprint=db_query("SELECT * FROM $LM_EVEDB.`invBlueprintTypes` WHERE `productTypeID` = $nr;");
	$techLevel=$blueprint[0][4];
	$wasteFactor=$blueprint[0][11]/100;
	$produceditem=db_query("SELECT * FROM $LM_EVEDB.`invBlueprintTypes` WHERE `blueprintTypeID` = $nr;");
	
	$node=getMarketNode($item['marketGroupID']);
	$breadcrumbs="&gt; ${item['typeName']}";
	$parentGroupID=$node['parentGroupID'];
	do {
		$breadcrumbs="&gt; <a href=\"?id=10&id2=0&marketGroupID=${node['marketGroupID']}\">${node['marketGroupName']}</a> $breadcrumbs";
		if (!empty($node['parentGroupID'])) {
			$node=getMarketNode($node['parentGroupID']);
		} else {
			break;
		}
		
	} while(TRUE);
	echo("<a href=\"?id=10&id2=0\"> Start </a> $breadcrumbs <br />");


        
        //echo("<h2>${item['typeName']}</h2>");
        //
//CATEGORY ID DEBUG        
        
        //echo("DEBUG: categoryID=".$item['categoryID']."<br/>");



    
if ($model=getResourceFromYaml($nr)) {
    //CCP WebGL -- 3D Preview!
    $skins = getShipSkins($nr);
    $racial = getAllRacialSkins($model['sofRaceName']);
    
    //var_dump($model);
?>
<?php if (!$MOBILE) { ?>
    <div id="3dpreview" style="width: 100%; min-width: 720px; display: none;">
<?php } else { ?>
    <div id="3dpreview" style="width: 100%; display: none;">
<?php } ?>
<table class="lmframework" style="width: 100%;">
    <tr><th colspan="2">3D Preview <img src="<?=getUrl()?>img/del.gif" alt="x" onclick="toggler('3dpreview'); scene=null; ship=null;" value="x" style="float: right;"/></th></tr>
    <tr><td width="725">
            <div style="width: 720px; height: 420px; background: url(<?php echo(getTypeIDicon($item['typeID'],512)); ?>) no-repeat center center; background-size: cover;">
                <canvas id="wglCanvas" width="720" height="420" style="width: 720px; height: 420px;"></canvas>
            </div>
    <input type="button" id="buttonFull" value="Fullscreen" style="position: relative; top: -418px; left: 2px; z-index: 10;" onclick="togglefull();"/></td>
    <td style="vertical-align: top;">
		<script type="text/javascript" src="<?=getUrl()?>ccpwgl/external/glMatrix-0.9.5.min.js"></script>
		<script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl_int.js"></script>
		<script type="text/javascript" src="<?=getUrl()?>ccpwgl/test/TestCamera2.js"></script>
		<script type="text/javascript" src="<?=getUrl()?>ccpwgl/ccpwgl.js"></script>
                <script type="text/javascript" src="<?=getUrl()?>webgl.js"></script>
		<script type="text/javascript">
                    settings.canvasID = 'wglCanvas';
                    settings.sofHullName = '<?=$model['sofHullName']?>';
                    settings.sofRaceName = '<?=$model['sofRaceName']?>';
                    settings.sofFactionName = '<?=$model['sofFactionName']?>';
                    settings.background = '<?=$model['background']?>';
                    settings.categoryID = <?=$item['categoryID']?>;
                    settings.volume = <?=$item['volume']?>;
                    settings.graphicFile = '<?=$model['graphicFile']?>';
		</script> 
		<div id="skinpanel">
		<?php if (count($skins)>0) showSkins($skins); else echo('<table><tr><th>Ship has no in-game SKINs</th></tr></table>'); ?>
                <?php if (count($racial)>0) showAllRacialSkins($racial); ?>
                <?php showWglCtrlPanel(); ?>
		</div>
    </td>
    
    </tr>
</table>
</div> 

             
                
<?php
    } //end if

    
//SHOW INFO AND ATTRIBUTES
?>

<?php if (!$MOBILE) { ?>
    <table cellspacing="2" cellpadding="0" style="width: 100%; min-width: 718px;">
<?php } else { ?>
    <table cellspacing="2" cellpadding="0" style="width: 100%;">
<?php } ?>      
        
<tr>
    
<?php if (!$MOBILE) { ?> 
    <td style="vertical-align: top; width:50%;">
<?php } else { ?>
    <td style="vertical-align: top; width:100%;">
<?php } ?>   
        
<table class="lmframework" style="width:100%;">
<tr><th colspan="2">
<strong>Show info</strong>
</th>
</tr>
<tr><td class="tab" style="padding: 0px; width: 32px;">
<?php if ($item['groupID']!=1311) { 
        if ($model) echo("<a href=\"".getTypeIDicon($item['typeID'],512)."\" target=\"_blank\">");
        ?>
	<img src="<?php echo(getTypeIDicon($item['typeID'],64));?>" title="<?php echo($item['typeName']); ?>" id="miniature" />
<?php 
        if ($model) echo("</a>");
} else {
	$skin = getSkin($nr);
	if (count($skin)>0) displaySkinIcon($skin[0],64);
} ?>
</td><td class="tab" style="text-align: center;"><h2>
<?php echo($item['typeName']);
if ($model) {
    ?>
    <input type="button" id="3dbutton_main" onclick="toggler('3dpreview'); loadPreview(settings,'default');" value="3D" disabled/>
    <script type="text/javascript">
        if (WGLSUPPORT) {
            document.getElementById('3dbutton_main').disabled=false;
            document.getElementById('3dbutton_main').title="Click to preview the 3D model";
        } else {
            document.getElementById('3dbutton_main').title="Your browser does not support WebGL";
        }
    </script>    
    <?php
}
?>
</h2></td>
</tr>
<tr><td class="tab" colspan="2">
<?php
//TRAITS, BONUSES, DESCRIPTION
	$traitData=db_asocquery("SELECT yit.*, eun.displayName
                FROM `$LM_EVEDB`.`yamlInvTraits` yit
                LEFT JOIN `$LM_EVEDB`.`eveUnits` eun
                ON yit.`unitID`=eun.`unitID`
                WHERE `typeID`=$nr AND `skillID`=-1;");
        $bonusData=db_asocquery("SELECT yit.*, eun.displayName
                FROM `$LM_EVEDB`.`yamlInvTraits` yit
                LEFT JOIN `$LM_EVEDB`.`eveUnits` eun
                ON yit.`unitID`=eun.`unitID`
                WHERE `typeID`=$nr AND `skillID`!=-1;");
	
	if (count($traitData) > 0) {
		echo('<strong>Traits</strong><br/><ul>');
                foreach($traitData as $row) { //<a href=showinfo:3307>
                    echo("<li>".$row['bonus'].$row['displayName']." ".preg_replace_callback('/\<a href=showinfo:(\d+)\>/',url_replace,$row['bonusText'] )."</li>");
                }
                echo('</ul>');
	} 
        if (count($bonusData) > 0) {
		echo('<strong>Bonuses</strong><br/><ul>');
                foreach($bonusData as $row) {
                    echo("<li>".$row['bonus'].$row['displayName']." ".preg_replace_callback('/\<a href=showinfo:(\d+)\>/',url_replace,$row['bonusText'] )." per level</li>");
                }
                echo('</ul>');
	} 
?>
<strong>Description</strong><br/><br/>
<?php 
	$descript=strip_tags($item['description'],'<b><i>');
	$descript=str_replace("\r",'',$descript);
	$descript=str_replace("\n",'<br />',$descript);
	echo($descript);
?>
</td>
</tr>
</table>

<?php
//SKINS
	showSkins($skins);

//PRICE DATA
	$priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=$nr;");
        $crestPriceData=db_asocquery("SELECT * FROM `crestmarketprices` WHERE `typeID`=$nr;");
	
	if (count($priceData) > 0) {
		$when=db_asocquery("SELECT `date` FROM `apistatus` WHERE `fileName`='eve-central.com/marketstat.xml';");
		$when=$when[0]['date'];	
		?>
		<table class="lmframework" style="width:100%;">
                    <tr><th colspan="5">eve-central.com prices (updated: <?php echo($when); ?>)</th></tr>
		<tr><th>
		Type
		</th><th>
		Average
		</th><th>
		Max
		</th><th>
		Min
		</th><th>
		Median
		</th></tr>
		<?php
		foreach($priceData as $row) {
			echo('<tr><td class="tab">');
				echo($row['type']);
			echo('</td><td class="tab">');
				echo(number_format($row['avg'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			echo('</td><td class="tab">');
			if ($row['type']=='buy') echo('<strong>');
				echo(number_format($row['max'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			if ($row['type']=='buy') echo('</strong>');
			echo('</td><td class="tab">');
			if ($row['type']=='sell') echo('<strong>');
				echo(number_format($row['min'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			if ($row['type']=='sell') echo('</strong>');
			echo('</td><td class="tab">');
				echo(number_format($row['median'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			echo('</td></tr>');
		}
        ?> </table> <?php
	} 
        if (count($crestPriceData) > 0) {
		$when=db_asocquery("SELECT `date` FROM `apistatus` WHERE `fileName`='CREST /market/prices/';");
		$when=$when[0]['date'];	
		?>
		<table class="lmframework" style="width:100%;">
                    <tr><th colspan="2">CREST price data (updated: <?php echo($when); ?>)</th></tr>
		<tr><th>
		Adjusted Price
		</th><th>
		Average Price
		</th></tr>
		<?php
		foreach($crestPriceData as $row) {
			echo('<tr><td class="tab">');
				echo(number_format($row['adjustedPrice'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			echo('</td><td class="tab">');
				echo(number_format($row['averagePrice'], 2, $DECIMAL_SEP, $THOUSAND_SEP));
			echo('</td></tr>');
		}
        ?> </table> <?php
	} 
        
        if ($pricesDisabled!='disabled') {
            ?>
		<table class="lmframework" style="width:100%;">
                    <tr><th>External price checks</th></tr>
		<tr>
                    <td>&raquo; <a href="http://eve-central.com/home/quicklook.html?typeid=<?php echo($nr); ?>" target="_blank">Check on eve-central.com</a></td>
                </tr>
                </table>
                
                
            <?php
        }
 
        
//MANUFACTURING	
	/************************* Manufacturing panel *******************************/
	//if itemID has a bluperint, it is manufacturable
	
	if (count($blueprint) > 0 ) {
		$bpo=$blueprint[0]; //blueprint TypeID
                
                //ME and PE settings
		if ($set=getMEPE($nr)) {
			$melevel=$set['me'];
			$pelevel=$set['pe'];
		}
		switch ($techLevel) {
			case 2:
				if (!isset($melevel)) $melevel=2;
				if (!isset($pelevel)) $pelevel=2;
				break;
			case 3:
				if (!isset($melevel)) $melevel=0;
				if (!isset($pelevel)) $pelevel=0;
				break;
			default:
				if (!isset($melevel)) $melevel=0;
				if (!isset($pelevel)) $pelevel=0;
		}
                
//PRODUCTION COSTS - AJAX
                echo('<div id="quote"></div>'); //ajax hook
		echo("<script type=\"text/javascript\"> ajax_get('ajax.php?act=GET_QUOTE&typeID=$nr','quote'); </script>");

//SKILLS
        	displaySkills(getSkills($bpo[0],1));

		?>
		<script type="text/javascript" src="<?=getUrl()?>skrypty.js"></script>
		<script type="text/javascript">
			function func(s) {
				//var s=document.getElementById('melevel');
				ajax_get('ajax.php?act=GET_MATERIALS&typeID=<?php echo($nr); ?>&melevel='+s.value,'materials');
			}
			
			function save_mepe() {
				var me=document.getElementById('melevel').value;
				var pe=document.getElementById('pelevel').value;
				ajax_save('index.php?id=10&id2=5&nr=<?php echo($nr); ?>&me='+me+'&pe='+pe,'save_me','save_me_label');
                                ajax_get('ajax.php?act=GET_QUOTE&typeID=<?php echo($nr); ?>','quote');
			}
		</script>
		<?php
		
//ME and PE form		
		echo('<table class="lmframework" width="100%"><tr><th colspan="3">Materials <span id="save_me_label"></span></th></tr><tr><td style="text-align: center;">');
		echo('<strong>ME: </strong>');
		echo('<input type="text" id="melevel" onclick="select_all(this);" onkeyup="func(this);" size="6" value="'.$melevel.'">');		
		echo('</td><td style="text-align: center;">');
		echo('<strong>TE: </strong>');
		echo('<input type="text" id="pelevel" onclick="select_all(this);" size="6" value="'.$pelevel.'">');		
		echo('</td>');
		if (checkrights("Administrator,EditMEPE")) {
			echo('<td style="text-align: center;">');
			echo("<input type=\"button\" id=\"save_me\" value=\"Save\" onclick=\"save_mepe();\"> ");
			echo('</td>');
		}
		
		echo('</tr></table>');
		
//MATERIALS - AJAX
		echo('<div id="materials"></div>'); //ajax hook
		echo("<script type=\"text/javascript\"> ajax_get('ajax.php?act=GET_MATERIALS&typeID=$nr&melevel=$melevel','materials'); </script>");		
	}

	/************************* END OF MATERIALS *******************************/

//META TYPES
	$metaTypes=db_asocquery("SELECT DISTINCT * FROM 
                (SELECT imt.*,inv.`typeName`
                FROM `$LM_EVEDB`.`invMetaTypes` imt
                JOIN `$LM_EVEDB`.`invTypes` inv
                ON imt.`typeID`=inv.`typeID`
                WHERE imt.`parentTypeID`=$nr
                UNION
                SELECT inv.`typeID`,0,1,inv.`typeName`
                FROM `$LM_EVEDB`.`invTypes` inv
                JOIN `$LM_EVEDB`.`invMetaTypes` imt
                ON inv.`typeID`=imt.`parentTypeID`
                WHERE inv.`typeID`=$nr
                UNION
                SELECT imtc.*,inv.`typeName`
                FROM `$LM_EVEDB`.`invMetaTypes` imtp
                JOIN `$LM_EVEDB`.`invMetaTypes` imtc
                ON imtp.`parentTypeID`=imtc.`parentTypeID`
                JOIN `$LM_EVEDB`.`invTypes` inv
                ON imtc.`typeID`=inv.`typeID`
                WHERE imtp.`typeID`=$nr
                UNION
                SELECT inv.`typeID`,0,1,inv.`typeName`
                FROM `$LM_EVEDB`.`invMetaTypes` imtp
                JOIN `$LM_EVEDB`.`invMetaTypes` imtc
                ON imtp.`parentTypeID`=imtc.`parentTypeID`
                JOIN `$LM_EVEDB`.`invTypes` inv
                ON imtc.`parentTypeID`=inv.`typeID`
                WHERE imtp.`typeID`=$nr) AS metaTypes
                ORDER BY `typeName`;");
	
	if (count($metaTypes) > 0) {

		?>
		<table class="lmframework" style="width:100%;">
                    <tr><th colspan="5">Meta types</th></tr>
		<!--<tr><th>
		Icon
		</th><th>
		Type Name
		</th></tr>-->
		<?php
       
		foreach($metaTypes as $row) {
			echo('<tr><td width="32" style="padding: 0px; text-align: center;">');
				hrefedit_item($row['typeID']);
				echo("<img src=\"".getTypeIDicon($row['typeID'])."\" title=\"${row['typeName']}\" />");
				echo('</a>');
			echo('</td><td>');
				hrefedit_item($row['typeID']);
				echo($row['typeName']);
				echo('</a>');
			echo('</td></tr>');
		}
        ?> </table> 
        
            
            <?php
	}
        

//API URLs 
        ?>
            <table class="lmframework" style="width:100%;">
                    <tr><th>API invTypes URLs</th></tr>
		<tr>
                    <?php
                    $keys=db_asocquery("SELECT * FROM `lmnbapi` lma LEFT JOIN `$USERSTABLE` lmu ON lma.`userID`=lmu.`userID` WHERE lma.`userID`=${_SESSION['granted']};");
                    if (count($keys)>0) $apikey='key='.$keys[0]['apiKey'].'&'; else $apikey='';
                    ?>
                    <td>LMeve API <a href="api.php?<?=$apikey?>endpoint=INVTYPES&typeID=<?=$nr?>" target="_blank">api.php?<?=$apikey?>endpoint=INVTYPES&typeID=<?=$nr?></a></td>
                </tr>
                <tr>
                    <td>CREST <a href="<?=$CREST_BASEURL?>/types/<?=$nr?>/" target="_blank"><?=$CREST_BASEURL?>/types/<?=$nr?>/</a></td>
                </tr>
            </table>
        <?php
        
if (!$MOBILE) {
    echo('</td><td style="width: 50%; vertical-align: top;">');
} else {
    echo('</td></tr><tr><td style="width: 100%; vertical-align: top;">');
}

		
	/****************************************************************************/
	/* CODE BELOW IS COPIED FROM ANOTHER APP. IT NEEDS TO BE DESPAGHETTIFIED!!  */
	/****************************************************************************/
	
	
	/************************* START OF DOGMA *******************************/
	echo("<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 100%;\">");
	echo('<tr><td colspan="2" class="tab-header"><strong>Attributes</strong></td></tr>');
	//echo("<tr><td width=\"50%\"><b>Size/Radius:</b></td><td width=\"50%\">${item[5]} m</td></tr>");
        //if item is a SKIN
        if ($item['groupID']=1311) {
            $ship=getShipBySkin($nr);
            if ($ship) echo("<tr><td class=\"tab\"><b>SKIN applies to:</b></td><td class=\"tab\"><a href=\"?id=10&id2=1&nr=${ship['typeID']}\"><img src=\"".getTypeIDicon($ship['typeID'])."\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px; margin-right: 3px;\" /> ${ship['typeName']}</a></td></tr>"); 
        }
        //continue
	echo("<tr><td class=\"tab\"><b>Mass:</b></td><td class=\"tab\">".number_format($item['mass'], 0, $DECIMAL_SEP, $THOUSAND_SEP)." kg</td></tr>");
	echo("<tr><td class=\"tab\"><b>Volume (unpacked):</b></td><td class=\"tab\">".number_format($item['volume'], 0, $DECIMAL_SEP, $THOUSAND_SEP)." m<sup>3</sup></td></tr>");
	echo("<tr><td class=\"tab\"><b>Cargohold:</b></td><td class=\"tab\">".number_format($item['capacity'], 2, $DECIMAL_SEP, $THOUSAND_SEP)." m<sup>3</sup></td></tr>");
	echo("<tr><td class=\"tab\"><b>Baseprice:</b></td><td class=\"tab\">".number_format($item['basePrice'], 2, $DECIMAL_SEP, $THOUSAND_SEP)." ISK</td></tr>");
        
        if ($item['portionSize']>1) $items='items'; else $items='item';
        echo("<tr><td class=\"tab\"><b>Portion size:</b></td><td class=\"tab\">".$item['portionSize']." $items</td></tr>");
        
	if (count($blueprint) > 0 ) {
		$blueprint=$blueprint[0];
		echo("<tr><td class=\"tab\"><b>Blueprint:</b</td><td class=\"tab\"><a href=\"?id=10&id2=1&nr=${blueprint[0]}\"><img src=\"ccp_icons/38_16_208.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" /> look up</a></td></tr>");
	}
	
	if (count($produceditem) > 0 ) {
		$produceditem=$produceditem[0];
		echo("<tr><td class=\"tab\"><b>Produced item:</b></td><td class=\"tab\"><a href=\"?id=10&id2=1&nr=${produceditem[2]}\"><img src=\"ccp_icons/38_16_208.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" /> look up</a></td></tr>");
		if ($produceditem[4]==2) echo("<tr><td class=\"tab\"><b>Base Invention chance:</b></td><td class=\"tab\">${produceditem[12]}</td></tr>");
	}

	$sql="SELECT COALESCE(valueFloat,valueInt),COALESCE(valueFloat,valueInt),displayName,description
	FROM $LM_EVEDB.`dgmTypeAttributes` AS dta
	JOIN $LM_EVEDB.`dgmAttributeTypes` AS da
	ON dta.attributeID=da.attributeID
	WHERE dta.typeID=$nr
	AND displayName != '';";
	$attr=db_query($sql); //dogma! [0]=valueFloat [1]=valueInt [2]=displayName [3]=description
	foreach ($attr as $element) {
		if (preg_match("/.*resistance$/i", $element[2], $regs)) {
			$element[0]=sprintf("%6.1f%%",100*(1.0-$element[0]));
		}
		if (preg_match("/.*skill.*/i", $element[2], $regs)) {
			if (!empty($element[1])) $skill=db_query("SELECT typeName FROM $LM_EVEDB.`invTypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/50_64_11.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" />  %s</a>",$element[1],$skill[0][0]);
		}
		if ($element[2]=="Used with (chargegroup)") {
			if (!empty($element[1])) $groupid=db_query("SELECT groupName FROM $LM_EVEDB.`invGroups` WHERE groupID = ${element[1]};");
			$element[0]=sprintf("%s",$groupid[0][0]);
		}
		if (strstr("Can be fitted to",$element[2])) {
                    //echo("DEBUG: 0=".$element[0].' 1='.$element[1].' 2='.$element[2].'<br/>');
                        if (!empty($element[0])) {
                            $canbeID=$element[0];
                        } else if (!empty($element[1])) {
                            $canbeID=$element[1];
                        }
			if (!empty($canbeID)) {
                            $groupid=db_asocquery("SELECT `groupName` FROM $LM_EVEDB.`invGroups` WHERE groupID = $canbeID;");
                            if (!empty($groupid)) {
                                $element[0]=$groupid[0]['groupName'];
                            } else {
                                $groupid=db_asocquery("SELECT `typeName` FROM $LM_EVEDB.`invTypes` WHERE typeID = $canbeID;");
                                $element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/38_16_208.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" /> %s</a>",$canbeID,$groupid[0]['typeName']);
                            }
                        }
		}
		//Jump Drive Fuel Need
		if (strstr("Jump Drive Fuel Need",$element[2])) {
			if (!empty($element[1])) $fuel=db_query("SELECT typeName FROM $LM_EVEDB.`invTypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"".getTypeIDicon($element[1])."\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" />  %s</a>",$element[1],$fuel[0][0]);
		}
		if (preg_match("/.*duration$/i", $element[2], $regs)) {
			$element[0]=sprintf("%d s",0.001*$element[0]);
		}
		if (preg_match("/Drone Capacity|.*Bay Capacity|.*Hangar Capacity|.*Hold Capacity/i", $element[2], $regs)) {
			$element[0]=number_format($element[0], 0, $DECIMAL_SEP, $THOUSAND_SEP)." m<sup>3</sup>";
		}
		if (preg_match("/.*Velocity$/i", $element[2], $regs)) {
			$element[0]=sprintf("%d m/s",$element[0]);
		}
		if (preg_match("/Bandwidth/i", $element[2], $regs)) {
			$element[0]=sprintf("%d MB/s",$element[0]);
		}
		if (strstr("Planet Type Restriction",$element[2])) {
			if (!empty($element[1])) $planettype=db_query("SELECT typeName FROM $LM_EVEDB.`invTypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/102_128_4.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" />  %s</a>",$element[1],$planettype[0][0]);
		}

		if (preg_match("/Warp Speed Multiplier/i",$element[2], $regs)) {
			$element[0]=sprintf("%6.2f AU/s",$element[0]);
			//$element[2]="Warp Speed";
		}
                if (preg_match("/powergrid usage/i",$element[2], $regs)) {
			$element[0]=sprintf("%d MW",$element[1]);
			//$element[2]="Warp Speed";
		}
                if (preg_match("/CPU usage/i",$element[2], $regs)) {
			$element[0]=sprintf("%d tf",$element[1]);
			//$element[2]="Warp Speed";
		}
		if (preg_match("/.*echarge time$/i", $element[2], $regs) || strstr("Rate of fire",$element[2])) {
			$element[0]=sprintf("%d s",0.001*$element[0]);
		}
		if (strstr("Consumption Type",$element[2])) {
			$type=db_query("SELECT typeName FROM $LM_EVEDB.invTypes WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/51_64_11.png\" style=\"width: 16px; height: 16px; float: left; margin-right: 3px;\" /> %s</a>",$element[1],$type[0][0]);
		}
		//echo("<tr><td><b>${element[2]}</b><br /><i>${element[1]}</i></td><td>${element[0]}</td></tr>");
		if (isset($element[0])) $value=$element[0]; else $value=$element[1];
		echo("<tr><td class=\"tab\"><b>${element[2]}</b><br /></td><td class=\"tab\">$value</td></tr>");
	}
	echo('</table>');
	/************************* END OF DOGMA *******************************/
	

	
	
	echo('</td></tr></table>');
?>

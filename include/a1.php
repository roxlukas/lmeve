<?
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

global $LM_EVEDB, $LM_CCPWGL_URL, $LM_CCPWGL_USEPROXY;
include_once('materials.php');
include_once('yaml_graphics.php');

$nr=secureGETnum('nr');

?>	    
		<script type="text/javascript" src="ajax.js"></script>
		<div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>
	<?php
		if (empty($nr)) {
			echo('Wrong parameter nr.');
			return;
		}
		?>

		<table cellpadding="0" cellspacing="2" width="95%">
	    <tr>
	    
	    <td style="width: 20%; min-width: 182px;">
		<form method="get" action="">
		<input type="text" name="query" value="" size="20">
		<input type="hidden" name="id" value="10">
		<input type="hidden" name="id2" value="2">
	    <input type="submit" value="Search">
		</form>
		</td>
		<?php
//TASKS INTEGRATION
		if (checkrights("Administrator,EditTasks")) {
			echo('<td  style="width: 35%;">');
			$tasks=db_asocquery("SELECT itp.`typeID`, itp.`typeName`, ibt.`blueprintTypeID`, iit.`productTypeID`, ibt.`techLevel` AS bpoTechLevel, iit.`techLevel` AS itemTechLevel
			FROM $LM_EVEDB.`invtypes` itp
			LEFT JOIN $LM_EVEDB.`invblueprinttypes` ibt
			ON itp.typeID=ibt.blueprintTypeID
			LEFT JOIN $LM_EVEDB.`invblueprinttypes` iit
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
					if ($tasks['bpoTechLevel']==2) echo('<input type="button" value="Invention" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=8\';">');
					if ($tasks['bpoTechLevel']==3) echo('<input type="button" value="Reverse Eng." onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=7\';">');
					echo('<input type="button" value="Copying" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=5\';">');
					echo('<input type="button" value="ME" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=4\';">');
					echo('<input type="button" value="PE" onclick="location.href=\'?id=1&id2=1&nr=new&typeID='.$nr.'&activityID=3\';">');
					$isBlueprint=TRUE;
				}
			}
			echo('</td>');
		}
                $hasMarketGroup=db_asocquery("SELECT * FROM $LM_EVEDB.`invtypes` WHERE `typeID`=$nr AND `marketGroupID` IS NOT NULL;");
		if (count($hasMarketGroup)>0) {
			$pricesDisabled='';
		} else {
			$pricesDisabled='disabled';
		}
		if (checkrights("Administrator,EditPricesFlag")) {
			echo('<td style="width: 12%; min-width: 110px;">');
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
			echo('<td style="width: 13%; min-width: 130px;">');
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
			echo('<td style="width: 20%; min-width: 180px;">');
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
                        echo(' stock: <input '.$pricesDisabled.' type="text" name="stockamount" id="stockamount" size="8" title="Press Enter to save the amount" value="'.$stockAmount.'" onchange="ajax_save(\'index.php?id=10&id2=6&nr='.$nr.'&update=1&amount=\'+getStockAmount(\'stockamount\'),\'cfgstock\',\'cfgstock_label\');" /></strong>');
			echo('<div id="cfgstock_label" style="position: fixed;"></div>');
			echo('</td>');
		}
		?>
		
		</tr></table>

		<?php
	
		$item=db_asocquery("SELECT itp.*,igp.`categoryID`
		FROM $LM_EVEDB.`invtypes` itp
                JOIN $LM_EVEDB.`invgroups` igp
                ON itp.`groupID`=igp.`groupID`
		WHERE `typeID` = $nr ;");
		$item=$item[0];
//BREADCRUMB NAVIGATION
	function getMarketNode($marketGroupID) {
		global $LM_EVEDB;
		if (empty($marketGroupID)) return;
		$data=db_asocquery("SELECT * FROM $LM_EVEDB.`invmarketgroups` WHERE `marketGroupID` = $marketGroupID ;");
		if (sizeof($data)==1) return($data[0]); else return;
	}
	
	$blueprint=db_query("SELECT * FROM $LM_EVEDB.`invblueprinttypes` WHERE `productTypeID` = $nr;");
	$techLevel=$blueprint[0][4];
	$wasteFactor=$blueprint[0][11]/100;
	$produceditem=db_query("SELECT * FROM $LM_EVEDB.`invblueprinttypes` WHERE `blueprintTypeID` = $nr;");
	
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

//CCP WebGL -- 3D Preview!
        
    if ($model=getResourceFromYaml($nr)) {
    
?>
<script type="text/javascript" src="./ccpwgl/external/glMatrix-0.9.5.min.js"></script>
<script type="text/javascript" src="./ccpwgl/ccpwgl_int.js"></script>
<script type="text/javascript" src="./ccpwgl/test/TestCamera2.js"></script>
<script type="text/javascript" src="./ccpwgl/ccpwgl.js"></script>
<script type="text/javascript">
function loadPreview()
            {
                <?php //check if we use proxy or not. If so, use proxy.php path, otherwise go to CCP CDN ?>
                ccpwgl.setResourcePath('res', '<?php echo($LM_CCPWGL_USEPROXY ? 'ccpwgl/proxy.php?fetch=' : $LM_CCPWGL_URL); ?>');
                var canvas = document.getElementById('wglCanvas');
                ccpwgl.initialize(canvas);
                var scene = ccpwgl.loadScene('<?php echo($model['background']); ?>');
                sun = scene.loadSun('res:/dx9/model/lensflare/orange.red', undefined);
       		var camera = new TestCamera(canvas);
                camera.minDistance = 10;
                camera.maxDistance = 10000;
                camera.fov = 30;
                camera.distance = <?php
                    if ($item['volume']==0 && ($item['categoryID']==3 || $item['categoryID']==2) ) echo('100000'); else
                    if ($item['volume']<100) echo('30'); else 
                    if (($item['volume']>=100) && ($item['volume']<6000)) echo('50'); else
                    if (($item['volume']>=6000) && ($item['volume']<29000)) echo('150'); else
                    if (($item['volume']>=29000) && ($item['volume']<50000)) echo('250'); else
                    if (($item['volume']>=50000) && ($item['volume']<120000)) echo('500'); else
                    if (($item['volume']>=120000) && ($item['volume']<600000)) echo('1600'); else
                    if (($item['volume']>=600000)) echo('2500');
                ?>;
                camera.rotationX = 0.5;
                camera.rotationY = 0.1;
                camera.nearPlane = 1;
                camera.farPlane = 10000000;
                camera.minPitch = -0.5;
                camera.maxPitch = 0.65;
                ccpwgl.setCamera(camera);
                <?php
                    if ($item['categoryID']==6 || $item['categoryID']==18) {
                        //if ship, NPC or drone - use loadShip
                        echo("var ship = scene.loadShip('${model['shipModel']}', undefined);\r\n");
                        echo("ship.loadBoosters('${model['thrusters']}');");
                    } else if ($item['categoryID']==3 || $item['categoryID']==11 || $item['categoryID']==2) {
                        echo("var ship = scene.loadObject('${model['shipModel']}', undefined);\r\n");
                    } else {
                        //echo("var ship = scene.loadObject('${model['shipModel']}', undefined);");
                        $model = false;
                    }
                ?>
                

                
                
                <?php //ccpwgl.enablePostprocessing(true); ?>

        	ccpwgl.onPreRender = function () 
        	{ 
                    /*var shipTransform = ship.getTransform();
                    shipTransform[5] = shipTransform[15] = 1.0;
                    X = Y * (Math.PI / 180.0);
                    Y=Y+.1;
                    shipTransform[0]=Math.cos(X);
                    shipTransform[2]=Math.sin(X);
                    shipTransform[8]=-1 * Math.sin(X);
                    shipTransform[10]=Math.cos(X);
                    ship.setTransform(shipTransform);*/
        	};
        		
            }
            
function togglefull() {
    var canvas=document.getElementById('wglCanvas');
    var button=document.getElementById('buttonFull');
    if (canvas.style.position=="absolute") {
        //minimize!
        canvas.style.position="static";
        canvas.style.width="100%";
        canvas.style.height="420px";
        button.style.position="relative";
        button.style.left="2px";
        button.style.top="-418px";
        button.value="Fullscreen";
    } else {
        //maximize!
        canvas.style.position="absolute";
        canvas.style.top="0px";
        canvas.style.left="0px";
        canvas.style.width="100%";
        canvas.style.height="100%";
        button.style.position="absolute";
        button.style.left="2px";
        button.style.top="2px";
        button.value="Minimize";
    }
}

function checkwglsuprt() {
  if (!window.WebGLRenderingContext) {
      //window.alert("Cannot create WebGLRenderingContext. WebGL disabled.");
      return false;   
  }
  var canvas = document.getElementById('wglCanvas');
  var experimental = false;
  try { gl = canvas.getContext("webgl"); }
  catch (x) { gl = null; }
  
  if (gl == null) {
        try { gl = canvas.getContext("experimental-webgl"); experimental = true; }
        catch (x) { return false; }
        if (!gl) {
            return false;
        }
  }
  return true;
}
</script>
<div id="3dpreview" style="width: 70%; min-width: 718px; display: none;">
<table class="lmframework" style="width: 100%;">
    <tr><th>3D Preview</th><th style="width: 14px;"><img src="img/del.gif" alt="x" onclick="toggler('3dpreview');" value="x"/></th></tr>
    <tr><td colspan="2"><canvas id="wglCanvas" style="width:100%; height:420px;"></canvas><input type="button" id="buttonFull" value="Fullscreen" style="position: relative; top: -418px; left: 2px; z-index: 10;" onclick="togglefull();"/></td></tr>
</table>
</div>                  
                
<?php
    } //end if

    
//SHOW INFO AND ATTRIBUTES
?>
                
<table cellspacing="2" cellpadding="0" style="width: 70%; min-width: 718px;">
<tr><td style="vertical-align: top; width:55%;">

<table class="lmframework" style="width:100%;">
<tr><th colspan="2">
<strong>Show info</strong>
</th>
</tr>
<tr><td class="tab" style="padding: 0px; width: 32px;">
<img src="ccp_img/<?php echo($item[typeID]); ?>_64.png" title="<?php echo($item['typeName']); ?>" id="miniature" />
</td><td class="tab" style="text-align: center;"><h2>
<?php echo($item['typeName']);
if ($model) {
    ?>
    <input type="button" id="3dbutton" onclick="toggler('3dpreview'); loadPreview();" value="3D" disabled/>
    <script type="text/javascript">
        if (checkwglsuprt()) {
            document.getElementById('3dbutton').disabled=false;
            document.getElementById('3dbutton').title="Click to view the 3D model";
        } else {
            document.getElementById('3dbutton').title="Your browser does not support WebGL";
        }
    </script>    
    <?php
}
?>
</h2></td>
</tr>
<tr><td class="tab" colspan="2">
<strong>Description</strong><br/>
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
//PRICE DATA
	$priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=$nr;");
	
	if (count($priceData) > 0) {
		$when=db_asocquery("SELECT `date` FROM `apistatus` WHERE `fileName`='eve-central.com/marketstat.xml';");
		$when=$when[0]['date'];	
		?>
		<table class="lmframework" style="width:100%;">
                    <tr><th colspan="5">Price checks (updated: <?php echo($when); ?>)</th></tr>
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
				if (!isset($melevel)) $melevel=-4;
				if (!isset($pelevel)) $pelevel=-4;
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
		<script type="text/javascript" src="skrypty.js"></script>
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

	
	echo('</td><td style="width: 45%; vertical-align: top;">');
	
	
	/****************************************************************************/
	/* CODE BELOW IS COPIED FROM ANOTHER APP. IT NEEDS TO BE DESPAGHETTIFIED!!  */
	/****************************************************************************/
	
	
	/************************* START OF DOGMA *******************************/
	echo("<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 100%;\">");
	echo('<tr><td colspan="2" class="tab-header"><strong>Attributes</strong></td></tr>');
	//echo("<tr><td width=\"50%\"><b>Size/Radius:</b></td><td width=\"50%\">${item[5]} m</td></tr>");
	echo("<tr><td class=\"tab\"><b>Mass:</b></td><td class=\"tab\">".number_format($item['mass'], 0, $DECIMAL_SEP, $THOUSAND_SEP)." kg</td></tr>");
	echo("<tr><td class=\"tab\"><b>Volume (unpacked):</b></td><td class=\"tab\">".number_format($item['volume'], 0, $DECIMAL_SEP, $THOUSAND_SEP)." m<sup>3</sup></td></tr>");
	echo("<tr><td class=\"tab\"><b>Cargohold:</b></td><td class=\"tab\">".number_format($item['capacity'], 2, $DECIMAL_SEP, $THOUSAND_SEP)." m<sup>3</sup></td></tr>");
	echo("<tr><td class=\"tab\"><b>Baseprice:</b></td><td class=\"tab\">".number_format($item['basePrice'], 2, $DECIMAL_SEP, $THOUSAND_SEP)." ISK</td></tr>");

	if (count($blueprint) > 0 ) {
		$blueprint=$blueprint[0];
		echo("<tr><td class=\"tab\"><b>Blueprint:</b</td><td class=\"tab\"><a href=\"?id=10&id2=1&nr=${blueprint[0]}\"><img src=\"ccp_icons/38_16_208.png\" style=\"width: 16px; height: 16px; float: left;\" /> look up</a></td></tr>");
	}
	
	if (count($produceditem) > 0 ) {
		$produceditem=$produceditem[0];
		echo("<tr><td class=\"tab\"><b>Produced item:</b></td><td class=\"tab\"><a href=\"?id=10&id2=1&nr=${produceditem[2]}\"><img src=\"ccp_icons/38_16_208.png\" style=\"width: 16px; height: 16px; float: left;\" /> look up</a></td></tr>");
		if ($produceditem[4]==2) echo("<tr><td class=\"tab\"><b>Base Invention chance:</b></td><td class=\"tab\">${produceditem[12]}</td></tr>");
	}

	$sql="SELECT valueFloat,valueInt,displayName,description
	FROM $LM_EVEDB.`dgmtypeattributes` AS dta
	JOIN $LM_EVEDB.`dgmattributetypes` AS da
	ON dta.attributeID=da.attributeID
	WHERE dta.typeID=$nr
	AND displayName != '';";
	$attr=db_query($sql); //dogma! [0]=valueFloat [1]=valueInt [2]=displayName [3]=description
	foreach ($attr as $element) {
		if (eregi(".*resistance$", $element[2], $regs)) {
			$element[0]=sprintf("%6.1f%%",100*(1.0-$element[0]));
		}
		if (eregi(".*skill required$", $element[2], $regs)) {
			if (!empty($element[1])) $skill=db_query("SELECT typeName FROM $LM_EVEDB.`invtypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/50_64_11.png\" style=\"width: 16px; height: 16px; float: left;\" />  %s</a>",$element[1],$skill[0][0]);
		}
		if ($element[2]=="Used with (chargegroup)") {
			if (!empty($element[1])) $groupid=db_query("SELECT groupName FROM $LM_EVEDB.`invgroups` WHERE groupID = ${element[1]};");
			$element[0]=sprintf("%s",$groupid[0][0]);
		}
		if (eregi(".*Can be fitted to$", $element[2], $regs)) {
			if (!empty($element[1])) $groupid=db_query("SELECT groupName FROM $LM_EVEDB.`invgroups` WHERE groupID = ${element[1]};");
			$element[0]=sprintf("%s",$groupid[0][0]);
		}
		//Jump Drive Fuel Need
		if (strstr("Jump Drive Fuel Need",$element[2])) {
			if (!empty($element[1])) $fuel=db_query("SELECT typeName FROM $LM_EVEDB.`invtypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_img/${element[1]}_32.png\" style=\"width: 16px; height: 16px; float: left;\" />  %s</a>",$element[1],$fuel[0][0]);
		}
		if (eregi(".*duration$", $element[2], $regs)) {
			$element[0]=sprintf("%d s",0.001*$element[0]);
		}
		if (strstr("Drone Capacity",$element[2])) {
			$element[0]=sprintf("%d m<sup>3</sup>",$element[0]);
		}
		if (eregi(".*Velocity$", $element[2], $regs)) {
			$element[0]=sprintf("%d m/s",$element[0]);
		}
		if (eregi("Bandwidth", $element[2], $regs)) {
			$element[0]=sprintf("%d MB/s",$element[0]);
		}
		if (strstr("Planet Type Restriction",$element[2])) {
			if (!empty($element[1])) $planettype=db_query("SELECT typeName FROM $LM_EVEDB.`invtypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/102_128_4.png\" style=\"width: 16px; height: 16px; float: left;\" />  %s</a>",$element[1],$planettype[0][0]);
		}
		if (strstr("Can be fitted to",$element[2])) {
			if (!empty($element[1])) $fittedto=db_query("SELECT typeName FROM $LM_EVEDB.`invtypes` WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"> %s</a>",$element[1],$fittedto[0][0]);
		}
		//if (eregi(".*Damage Resistance$", $element[2], $regs)) {
		//	$element[0]=sprintf("%d %%",100*(1.0-$element[0]));
		//}
		if (eregi("Warp Speed Multiplier",$element[2], $regs)) {
			$element[0]=sprintf("%6.2f AU/s",$element[0]);
			//$element[2]="Warp Speed";
		}
		if (eregi(".*echarge time$", $element[2], $regs) || strstr("Rate of fire",$element[2])) {
			$element[0]=sprintf("%d s",0.001*$element[0]);
		}
		if (strstr("Consumption Type",$element[2])) {
			$type=db_query("SELECT typeName FROM $LM_EVEDB.invtypes WHERE typeID = ${element[1]};");
			$element[0]=sprintf("<a href=\"?id=10&id2=1&nr=%d\"><img src=\"ccp_icons/51_64_11.png\" style=\"width: 16px; height: 16px; float: left;\" /> %s</a>",$element[1],$type[0][0]);
		}
		//echo("<tr><td><b>${element[2]}</b><br /><i>${element[1]}</i></td><td>${element[0]}</td></tr>");
		if (isset($element[0])) $value=$element[0]; else $value=$element[1];
		echo("<tr><td class=\"tab\"><b>${element[2]}</b><br /></td><td class=\"tab\">$value</td></tr>");
	}
	echo('</table>');
	/************************* END OF DOGMA *******************************/
	

	
	
	echo('</td></tr></table>');
?>

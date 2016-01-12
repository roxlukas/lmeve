<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewInventory")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
include_once('inventory.php');
$MENUITEM=2; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Inventory Browser'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$DECIMAL_SEP,$THOUSAND_SEP;

?>
		<a name="top"></a>             
                
<div class="tytul">
    <?=$PANELNAME?><br/>
</div>
                
<?php
    $nr=secureGETnum('nr');
    $corporationID=secureGETnum('corporationID');
    if (empty($nr)) $nr=0;
    if (empty($corporationID)) $corporationID=0;
    if ($corporationID==0 && $nr==0) {
        $corps=db_asocquery("SELECT * FROM apicorps;");
        foreach ($corps as $corp) { //begin corps loop
            echo("<h1><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h1>");
            showInventory(getInventory($nr,$corp['corporationID']));
        }//end corps loop
    } else {
        $headerData=getInventoryHeader($nr,$corporationID);
        $data=getInventory($nr,$corporationID);
        showInventoryHeader($headerData,$corporationID);
        if (count($headerData)>0 && $headerData[0]['categoryID']==6 && $headerData[0]['singleton']==1) showInventoryFitting ($data, $headerData[0]['typeID']); else showInventory($data,$nr,$corporationID);
    }
    
?>
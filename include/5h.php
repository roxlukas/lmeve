<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='EVE Corp API keys'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

function apikeyhrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=20&nr=$nr\" title=\"Click to delete this key\">");
}

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>
<img src="<?=getUrl()?>ccp_icons/7_64_5.png"  alt="Corporation" style="float: left;"/>
<table><tr><td>
    <form method="post" action="?id=5&id2=18">
        <?php token_generate(); ?>
        <input type="submit" value="Add new API Key" /><br/>
    </form>
</td><td>
    <form method="post" action="?id=8&id2=4">
        <?php token_generate(); ?>
        <input type="submit" value="Check API Statistics" /><br/>
    </form>            
</td></tr></table>
<div><br/>
<img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(!) "/> LMeve <u>only</u> works with Corporation level API Keys.<br/>
<br/></div>
<table class="lmframework">
    <tr><th>
        Key ID
    </th><th>
        Verification
    </th><th>		
        Corporation
    </th><th>		
        Last used
    </th><th>		

    </th>
    </tr>
    <?php
    
    $keys=db_asocquery("SELECT cak.*,aps.date,apc.`corporationName`
            FROM `cfgapikeys` cak 
            LEFT JOIN (SELECT `keyID`,MAX(`date`) AS `date` FROM `apistatus` GROUP BY `keyID`) aps
            ON cak.`keyID`=aps.`keyID`
            LEFT JOIN `apicorps` apc
            ON cak.`keyID`=apc.`keyID`
            GROUP BY cak.keyID;");
    
    if (count($keys)>0) {
        foreach($keys as $key) {
            echo('<tr><td>');
            echo($key['keyID']);
            echo('</td><td>');
            echo(substr($key['vCode'],0,6).'*********************');
            echo('</td><td>');
            echo($key['corporationName']);
            echo('</td><td>');
            echo($key['date']);
            echo('</td><td>');
            apikeyhrefedit($key['apiKeyID']);
            echo('<img src="'.getUrl().'img/del.gif" alt="Delete key" />');
            echo("</a>");
            echo('</td></tr>');
        }
    }
    echo('</table>');
?>
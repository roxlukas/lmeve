<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='ESI API Tokens'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB;

include_once('ssofunctions.php');

function esihrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=24&nr=$nr\" title=\"Click to delete this token\">");
}

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>

<div style="float: left; min-width: 420px;">
<img src="<?=getUrl()?>ccp_icons/7_64_5.png"  alt="Corporation" style="float: left;"/>
<table><tr><td>
    <form method="post" action="?id=5&id2=22">
        <?php token_generate(); ?>
        <input type="submit" value="New ESI Token" /><br/>
    </form>
</td><td>
    <form method="post" action="?id=8&id2=4">
        <?php token_generate(); ?>
        <input type="submit" value="Check ESI Statistics" /><br/>
    </form>            
</td></tr></table>
<br/>


<img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(!) "/> LMeve <u>requires</u> Director level access to be able to use all ESI data.<br/>
<br/>
<table class="lmframework">
    <tr><th>
        Token ID
    </th><th>
        Refresh Token
    </th><th>		
        Corporation
    </th><th>		
        Last used
    </th><th>		

    </th>
    </tr>
    <?php
    
    $keys=db_asocquery("SELECT cet.*,aps.date,apc.`corporationName`
            FROM `cfgesitoken` cet 
            LEFT JOIN (SELECT `tokenID`,MAX(`date`) AS `date` FROM `esistatus` GROUP BY `tokenID`) aps
            ON cet.`tokenID`=aps.`tokenID`
            LEFT JOIN `apicorps` apc
            ON cet.`tokenID`=apc.`tokenID`
            GROUP BY cet.`tokenID`;");
    
    if (count($keys)>0) {
        foreach($keys as $key) {
            echo('<tr><td>');
            echo($key['tokenID']);
            echo('</td><td>');
            echo(substr($key['token'],0,6).'*********************');
            echo('</td><td>');
            echo($key['corporationName']);
            echo('</td><td>');
            echo($key['date']);
            echo('</td><td>');
            esihrefedit($key['tokenID']);
            echo('<img src="'.getUrl().'img/del.gif" alt="Delete key" />');
            echo("</a>");
            echo('</td></tr>');
        }
    }
    echo('</table>');
    
    
?>
</div>
<div style="float: right; min-width: 300px;"><h3><img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(!) "/> Required Scopes</h3>
    <?php showScopes(getLMeveCorpScopes()); ?>
</div>
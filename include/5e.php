<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,UseNorthboundApi")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=5; //Panel ID in menu. Used in hyperlinks
$PANELNAME='LMeve Northbound API keys'; //Panel name (optional)
//standard header ends here
global $LM_EVEDB, $USERSTABLE;

function nbapihrefedit($nr) {
    echo("<a href=\"index.php?id=5&id2=16&nr=$nr\" title=\"Click to delete this key\">");
}

?>
<div class="tytul">
<?php echo($PANELNAME); ?>
</div>
<form method="post" action="?id=5&id2=15">
    <?php token_generate(); ?>
    <input type="submit" value="Create key" /><br/>
</form>
<div><br/>
<img src="<?=getUrl()?>ccp_icons/38_16_208.png" alt="(!) "/> LMeve can provide information to other third party apps that support it, such as <a href="http://evernus.com/" target="_blank">Evernus</a> or <a href="http://caldariprimeponyclub.com/" target="_blank">Jeremy</a>.<br/>
<br/></div>
<table class="lmframework">
    <tr><th>
        API Key
    </th><th>
    <?php if (checkrights("Administrator")) {
                echo('User login</th><th>');  
            }
     ?>
        Last Used
    </th><th>		
        Last IP Address
    </th><th>
        
    </th>
    </tr>
    <?php
    if (!checkrights("Administrator")) {
        //if user is not admin, he can only delete their own keys
        $owner="lma.`userID`=${_SESSION['granted']}";
    } else {
        //if user is admin, he can delete any key
        $owner="TRUE";
    }
    $keys=db_asocquery("SELECT * FROM `lmnbapi` lma LEFT JOIN `$USERSTABLE` lmu ON lma.`userID`=lmu.`userID` WHERE $owner;");
    if (count($keys)>0) {
        foreach($keys as $key) {
            echo('<tr><td>');
            echo($key['apiKey']);
            echo('</td><td>');
            if (checkrights("Administrator")) {
                echo($key['login']);
                echo('</td><td>');  
            }
            echo($key['lastAccess']);
            echo('</td><td>');
            echo($key['lastIP']);
            echo('</td><td>');
            nbapihrefedit($key['apiKeyID']);
            echo('<img src="'.getUrl().'img/del.gif" alt="Delete key" />');
            echo("</a>");
            echo('</td></tr>');
        }
    }
    echo('</table>');
?>
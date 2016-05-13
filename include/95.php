<?php
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB;

if (!token_verify()) die("Invalid or expired token.");
$keyid=secureGETnum('keyid');
$verification=secureGETstr('verification');

function getCharInfo($id) {
	if (($id)==0) return FALSE;
	$api_url="https://api.eveonline.com/eve/CharacterInfo.xml.aspx?characterID=$id";
	
 	$data = file_get_contents($api_url);
        
        if ($data===false) {
            return false;
        }

        $xml=simplexml_load_string($data);
        $res=$xml->result;
        return $res;
}

function getCharacters($keyid,$verification) {
	$charactersURL="https://api.eveonline.com/account/Characters.xml.aspx";
	$charactersURL=$charactersURL."?keyID=".$keyid."&vCode=".$verification;
        
 	$data = file_get_contents($charactersURL);
        
        if ($data===false) {
            return false;
        }
       
        $xml=simplexml_load_string($data);
        $res=$xml->result->rowset->row;
        //<row name="Lukas Rox" characterID="816121566" corporationName="Aideron Technologies" corporationID="98126753" allianceID="0" allianceName="" factionID="0" factionName="" />
        return $res;
}

function isValidCorp($corporationID) {
    $count=db_count("SELECT `corporationID` FROM `apicorps` WHERE `corporationID`=$corporationID;");
    if ($count==1) return TRUE; else return FALSE;
}

function isInMembers($characterID) {
    $count=db_count("SELECT `characterID` FROM `apicorpmembers` WHERE `characterID`=$characterID;");
    if ($count==1) return TRUE; else return FALSE;
}

function filterByCorps($chars) {
    foreach($chars as $key => $toon) {
        $attrs=$toon->attributes();
        if (isValidCorp($attrs->corporationID)) $ret[$i++]=$toon;
    }
    return $ret;
}

function filterByMembersApi($chars) {
    foreach($chars as $key => $toon) {
        $attrs=$toon->attributes();
        if (isInMembers($attrs->characterID)) $ret[$i++]=$toon;;
    }
    return $ret;
}

function displayCharacters($chars) {
    if (count($chars)>0) {
        foreach($chars as $toon) {
            $attrs=$toon->attributes();
            ?>
            <img src="https://imageserver.eveonline.com/Character/<?php echo($attrs->characterID); ?>_64.jpg" alt="<?php echo($attrs->name); ?>" title="<?php echo($attrs->name); ?>" /> <img src="https://imageserver.eveonline.com/Corporation/<?php echo($attrs->corporationID); ?>_64.png" alt="<?php echo($attrs->corporationName); ?>" title="<?php echo($attrs->corporationName); ?>" /><br />
            <?php
        }   
    } else {
        echo('<strong>No characters!</strong>');
    }
}

function connectCharacters($chars) {
    $i=0;
    if (count($chars)>0) {
        foreach($chars as $toon) {
            $attrs=$toon->attributes(); 
            $sql="INSERT IGNORE INTO `lmchars` VALUES (
			".$attrs->characterID.",
			".$_SESSION['granted']."
			);";
            db_uquery($sql);
            $i++;
        }    
    }
    return $i;
}

$chars=getCharacters($keyid,$verification);

$valid_chars=filterByCorps($chars);

$final_chars=filterByMembersApi($valid_chars);

$connected=connectCharacters($final_chars);

?>
            <span class="tytul">
		<?php echo($PANELNAME); ?>
	    </span>
        
        <table class="lmframework">
            <tr><th>Characters available in Your personal API</th><td></td><th>Characters eligible to link</th><td></td><th>Characters available in Corporation API</th></tr>
            <tr><td style="text-align: center;"><?php displayCharacters($chars); ?></td><td><img src="<?=getUrl()?>ccp_icons/9_64_6.png" alt="-&gt;" /></td><td style="text-align: center;"><?php displayCharacters($valid_chars); ?></td><td><img src="<?=getUrl()?>ccp_icons/9_64_6.png" alt="-&gt;" /></td><td style="text-align: center;"><?php displayCharacters($final_chars); ?></td></tr>
        </table>
<?php
    if ($connected>0) {
        echo("<h3>$connected character(s) have been linked to your LMEve account.</h3>"); 
    } else {
        echo('<h3>No characters linked!</h3> If characters are eligible, but do not show in corporation API (last column), please try again later.');
    }
?>
                <form method="get" action="">
		<input type="hidden" name="id" value="9" />
		<input type="hidden" name="id2" value="0" />
		<input type="submit" value="OK" />
		</form>
        <!--
		<script type="text/javascript">location.href="index.php?id=9&id2=0";</script>
	    //-->

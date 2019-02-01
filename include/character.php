<?php

function getCharacterName($characterID) {
    if (!is_numeric($characterID)) return FALSE;
    $c = db_asocquery("SELECT * FROM  `apicorpmembers` WHERE `characterID` = $characterID");
    if (is_array($c) && count($c) > 0) {
        $c = $c[0];
        return $c['name'];
    }
}

function getCharacterInfoXML($id) {
	global $LM_XML_API_SERVER;
	if (($id)==0) return FALSE;
	$api_url="https://$LM_XML_API_SERVER/eve/CharacterInfo.xml.aspx?characterID=$id";
	//echo("api_url=$api_url<br/>");
	$cache="../var/CharacterInfo_$id.xml";

	if (file_exists($cache) && (filemtime($cache)>(time() - 60*60*24 ))) {
	    $data = file_get_contents($cache);
	} else {
 	    $data = file_get_contents($api_url);
            if ($data===false) {
                //http errors
            } else {
                file_put_contents($cache, $data, LOCK_EX);
            }
	}

        $xml=simplexml_load_string($data);
        $res=$xml->result;
        return $res;
}

function getCharInfoXML($id) {
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

function getCharactersXML($keyid,$verification) {
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

function getCharacters() {
	return db_asocquery("SELECT apc.* "
                . "FROM `lmchars` lmc "
                . "JOIN `apicorpmembers` apc ON lmc.`charID` = apc.`characterID`"
                . "WHERE `userID` = " . $_SESSION['granted']);
}

function getCharactersDropdown() {
    if (getConfigItem('only_linked_chars', 'disabled') == 'enabled') {
        $chars=db_asocquery("SELECT apc.`characterID` , apc.`name`
        FROM `apicorpmembers` apc
        JOIN `lmchars` lmc ON apc.`characterID` = lmc.`charID`
        ORDER BY name;");
    } else {
        $chars=db_asocquery("SELECT characterID, name FROM `apicorpmembers` ORDER BY name;");
    }
    return $chars;
}

function getCharacterPortrait($characterID, $size = 32) {
    if (!is_numeric($characterID)) $characterID=0;
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=256 && $size!=512)) $size=32;
    $icon="https://imageserver.eveonline.com/character/${characterID}_${size}.jpg";
    return($icon);
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


?>

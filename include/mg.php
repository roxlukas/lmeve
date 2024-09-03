<?php
/* 
    "SOCIAL SPACE" module for LMeve
    Created on : 2019-01-22, 10:31:03
    Author     : Lukas Rox
*/

// MG main file

include_once("materials.php");
include_once("yaml_graphics.php");
include_once("skins.php");
include_once("dbcatalog.php");
include_once("character.php");
include_once("inventory.php");

function mg_dbcatalog() {
    //mg_assets
    //itemID 	parentItemID 	characterID   solarSystemID 	typeID 	quantity 	flag    x   y   z
    if (!checkIfTableExistsLmeve('mg_assets')) {
        db_uquery("CREATE TABLE IF NOT EXISTS `mg_assets` (
        `itemID` bigint(11) NOT NULL AUTO_INCREMENT,
        `parentItemID` bigint(11) NULL DEFAULT NULL,
        `characterID` bigint(11) NOT NULL,
        `solarSystemID` bigint(11) NOT NULL,
        `typeID` bigint(11) NOT NULL,
        `quantity` bigint(11) NOT NULL,
        `flag` bigint(11) NULL DEFAULT NULL,
        `x` double NOT NULL,
        `y` double NOT NULL,
        `z` double NOT NULL,
        PRIMARY KEY (`itemID`),
        UNIQUE KEY (`parentItemID`,`flag`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100000000;");
    }
    
    //mg_session
    //characterID   shipItemID  state
    if (!checkIfTableExistsLmeve('mg_session')) {
        db_uquery("CREATE TABLE IF NOT EXISTS `mg_session` (
        `characterID` bigint(11) NOT NULL,
        `shipItemID` bigint(11) NOT NULL,
        `timestamp` datetime NOT NULL,
        `state` varchar(255) NOT NULL,
        PRIMARY KEY (`characterID`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
    
    //mg_queue
    //queueID   characterID actionTime action itemID targetID
    if (!checkIfTableExistsLmeve('mg_queue')) {
        db_uquery("CREATE TABLE IF NOT EXISTS `mg_queue` (
        `queueID` int(11) NOT NULL,    
        `characterID` bigint(11) NOT NULL,
        `actionTime` datetime NOT NULL,
        `action` varchar(255) NOT NULL,
        `itemID` bigint(11) NOT NULL,
        `targetID` bigint(11) NOT NULL,
        PRIMARY KEY (`queueID`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
    
    //mg_dogma
    //itemID dogma timestamp
    if (!checkIfTableExistsLmeve('mg_dogma')) {
        db_uquery("CREATE TABLE IF NOT EXISTS `mg_dogma` (
        `itemID` bigint(11) NOT NULL,
        `dogma` TEXT NULL,
        `timestamp` datetime NOT NULL,
        PRIMARY KEY (`itemID`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
    
    //DEFAULT,$time,$solarSystemID,$characterID,'$text'
    if (!checkIfTableExistsLmeve('mg_localchat')) {
        db_uquery("CREATE TABLE IF NOT EXISTS `mg_localchat` (
        `chatID` bigint(11) NOT NULL AUTO_INCREMENT,
        `timestamp` bigint(11) NOT NULL,
        `solarSystemID` bigint(11) NOT NULL,
        `characterID` bigint(11) NOT NULL,
        `text` TEXT NULL,
        PRIMARY KEY (`chatID`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
}

function mg_load_dogma($itemID) {
    if (!(is_numeric($itemID) && $itemID > 0)) return FALSE;
    //return stored dogma
    $dogma =  db_asocquery("SELECT *"
            . "FROM `mg_dogma` mgs "
            . "WHERE mgs.`itemID` = $itemID;");
    if (is_array($dogma) && count($dogma) > 0) {
        $dogma = $dogma[0];
        return unserialize($dogma['dogma']);
    } else {
        //if dogma does not exists, create a default one
        //get typeID
        $assets = db_asocquery("SELECT * FROM `mg_assets` WHERE `itemID`=$itemID LIMIT 1;");
        if (is_array($assets) && count($assets) > 0) {
            $assets = $assets[0];
            $dogma = getInvTypes($assets['typeID'], TRUE);
            $dogmastr = serialize($dogma);
            db_uquery("INSERT INTO `mg_dogma` VALUES ($itemID, '$dogmastr', NOW())");
            return $dogma;
        } else {
            return FALSE;
        }
        return FALSE;
    }
}

function mg_character_select_screen() {
    ?>
    <div id="mg_character_select_screen" style = "width: 800px; margin-left: auto; margin-right: auto;">
        <div class="grid-container lmframework">
            <div class="row">
                <?php
                $chars = getCharacters();
                if (is_array($chars) && count($chars) > 0) {
                    foreach($chars as $char) {
                        ?>
                        <div class="col-4" style="text-align: center;">
                            <a href="?id=253&select=<?=$char['characterID']?>"><img src="<?=getCharacterPortrait($char['characterID'], 256)?>" alt="<?=$char['name']?>"  title="<?=$char['name']?>" /></a><br/><?=$char['name']?>
                        </div>
                        <?php
                    }
                    for ($i = 0; $i < 3 - count($chars) % 3; $i++) {
                        ?>
                        <div class="col-4">
                            <div style="width: 256px; height: 256px; border: 1px rgba(255,255,255,0.1) solid;"></div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function vertical_align() {
            var t = document.getElementById('tab-main');
            var l = document.getElementById('tab-links');
            var ht = parseInt(t.style.getPropertyValue('height'));
            var s = document.getElementById('mg_character_select_screen');
            var hs = 256;
            var d = Math.round((ht - hs) / 2);
            s.style.setProperty('margin-top', d + 'px');
            l.style.setProperty('display', 'none');
            t.style.setProperty('background', "url('<?=getUrl()?>/img/mQYgq2Z.jpg') #0e1e28 no-repeat center top");
            t.style.setProperty('background-size', "cover");
            t.style.setProperty('padding', "0px");
        }

        window.addEventListener("load", vertical_align);
        window.addEventListener("resize", vertical_align);
    </script>
    <?php
}

function mg_connecting_screen($message = "Connecting...") {
    ?>
    <div id="mg_connecting_screen" style = "width: 800px; margin-left: auto; margin-right: auto; text-align: center;">
        <h1><?=$message?></h1>
    </div>
    <script type="text/javascript">
        function vertical_align() {
            var t = document.getElementById('tab-main');
            var l = document.getElementById('tab-links');
            var h = parseInt(t.style.getPropertyValue('height'));
            var s = document.getElementById('mg_connecting_screen');
            var m = Math.round((h - 26) / 2);
            if (s != null) s.style.setProperty('margin-top', m + 'px');
            l.style.setProperty('display', 'none');
            t.style.setProperty('background', "url('<?=getUrl()?>/img/mQYgq2Z.jpg') #0e1e28 no-repeat center top");
            t.style.setProperty('background-size', "cover");
            t.style.setProperty('padding', "0px");
        }

        window.addEventListener("load", vertical_align);
        window.addEventListener("resize", vertical_align);
    </script>
    <?php
}

function mg_load_session_data($characterID) {
    global $LM_EVEDB;
    $ls = db_asocquery("SELECT mgs.*, mga.*, map.`constellationID`, map.`regionID`, map.`solarSystemName`, con.`constellationName`, reg.`regionName`, map.`luminosity`, map.`security`, map.`factionID`, map.`sunTypeID`, itp.`groupID`, itp.`typeName`, itp.`description`, itp.`iconID`, itp.`soundID`, itp.`graphicID`, apc.`name`, sta.`stationName`, sta.`stationTypeID`, sta.`corporationID`, nam.`itemName` AS corporationName, crp.`factionID`, fac.`factionName`, fac.`iconID` as `factionIconID`, ico.`iconFile`"
            . "FROM `mg_session` mgs "
            . "JOIN `mg_assets` mga ON mgs.`shipItemId` = mga.`itemID` "
            . "JOIN `$LM_EVEDB`.`mapSolarSystems` map ON mga.`solarSystemID` = map.`solarSystemID` "
            . "JOIN `$LM_EVEDB`.`mapConstellations` con ON map.`constellationID` = con.`constellationID` "
            . "JOIN `$LM_EVEDB`.`mapRegions` reg ON map.`regionID` = reg.`regionID` "
            . "JOIN `$LM_EVEDB`.`invTypes` itp ON mga.`typeID` = itp.`typeID` "
            . "JOIN `apicorpmembers` apc ON mgs.`characterID` = apc.`characterID`"
            . "JOIN `$LM_EVEDB`.`chrFactions` fac ON map.`factionID` = fac.`factionID` "
            . "JOIN `$LM_EVEDB`.`eveIcons` ico ON fac.`iconID` = ico.`iconID` "
            . "LEFT JOIN `$LM_EVEDB`.`staStations` sta ON mga.`parentItemID` = sta.`stationID` "
            . "LEFT JOIN `$LM_EVEDB`.`crpNPCCorporations` crp ON sta.`corporationID` = crp.`corporationID` "
            . "LEFT JOIN `$LM_EVEDB`.`invNames` nam ON crp.`corporationID` = nam.`itemID` "            
            . "WHERE mgs.`characterID` = $characterID "
            . "LIMIT 1;");
    
    if (is_array($ls) && count($ls) > 0) {
        $ls = $ls[0];
        
        //last server tick
        $ls['server_time'] = getConfigItem('mg_tick');
        
        //load ship dogma
        $dogma = mg_load_dogma($ls['shipItemID']);
        $ls['dogma'] = $dogma;
        
        //detect ship state 'docked' or 'inspace'
        if (!empty($ls['parentItemID'])) {
            $ls['ship_state'] = 'docked';
        } else { 
            $ls['ship_state'] = 'inspace';
        }
        
        //local list
        $ls['local'] = mg_chat_local_get_list($ls['solarSystemID']);
        
        //change icon path
        $ls['iconFile'] = getUrl() . 'ccp_icons/' . basename($ls['iconFile']);
        
        //find nearest
        $nearest = db_asocquery("SELECT * FROM "
                . "(SELECT findNearest({$ls['x']}, {$ls['y']}, {$ls['z']}, {$ls['solarSystemID']}) AS itemID) obj "
                . "JOIN `$LM_EVEDB`.`mapDenormalize` nam ON obj.`itemID` = nam.`itemID`");
        if (is_array($nearest) && count($nearest) > 0) {   
            $nearest = $nearest[0];
            $ls['nearestObjectID'] = $nearest['itemID'];
            $ls['nearestObjectName'] = $nearest['itemName'];
            $ls['nearestObjectTypeID'] = $nearest['typeID'];
        }
        //return session data
        return $ls;      
    } else {
        return FALSE;
    }
}

function mg_load_short_session_data($characterID) {
    $short_session =  db_asocquery("SELECT *"
            . "FROM `mg_session` mgs "
            . "JOIN `mg_assets` mga ON mgs.`shipItemId` = mga.`itemID` "
            . "WHERE mgs.`characterID` = $characterID;");
    if (is_array($short_session) && count($short_session) > 0) {
        $short_session = $short_session[0];
        return $short_session;
    } else {
        return FALSE;
    }
}

function mg_create_ship($characterID, $typeID, $locationID) {
    global $LM_EVEDB;
    $location = db_asocquery("SELECT * FROM `$LM_EVEDB`.`mapDenormalize` WHERE `itemID`=$locationID;");
    if (is_array($location) && count($location) > 0) {
        $location = $location[0];
        db_uquery("INSERT INTO `mg_assets` VALUES(DEFAULT, $locationID, $characterID, {$location['solarSystemID']}, $typeID, 1, NULL, {$location['x']}, {$location['y']}, {$location['z']});");
        $assets = db_asocquery("SELECT * FROM `mg_assets` WHERE `characterID`=$characterID AND `typeID` = $typeID AND `solarSystemID` = {$location['solarSystemID']} ORDER BY `itemID` DESC LIMIT 1;");
        $shipItemID = $assets[0]['itemID'];
        return $shipItemID;
    } else {
        return FALSE;
    }
    
}

function mg_fit_item($shipItemID, $typeID, $flag) {
    if (!(is_numeric($itemID) && $itemID > 0)) return FALSE;
    if (!(is_numeric($shipItemID) && $shipItemID > 0)) return FALSE;
    $assets = db_asocquery("SELECT * FROM `mg_assets` WHERE `itemID`=$shipItemID LIMIT 1;");
    if (is_array($assets) && count($assets) > 0) {
        $assets = $assets[0];
        db_uquery("INSERT INTO `mg_assets` VALUES(DEFAULT, $shipItemID, {$assets['characterID']}, {$assets['solarSystemID']}, $typeID, 1, $flag, {$assets['x']}, {$assets['y']}, {$assets['z']});");
        $item = db_asocquery("SELECT * FROM `mg_assets` WHERE `characterID`={$assets['characterID']} AND `typeID` = $typeID AND `parentItemID` = $shipItemID AND `flag` = $flag ORDER BY `itemID` DESC LIMIT 1;");
        $itemID = $item[0]['itemID'];
        return $itemID;
    } else {
        return FALSE;
    }  
}

function mg_create_noobship($characterID) {
    $DEFAULT_SHIP = 606;
    $DEFAULT_LOCATION = 60014719;
    if (is_numeric($characterID) && $characterID > 0) {
    $apc = db_asocquery("SELECT * FROM `apicorpmembers` WHERE `characterID` = $characterID;");
    if (is_array($apc) && count($apc) > 0) {
        if (!empty($apc[0]['solarSystemID'])) $locationID = $apc[0]['solarSystemID']; else $locationID = $DEFAULT_LOCATION;
        if (!empty($apc[0]['shipID'])) $shipID = $apc[0]['shipID']; else $shipID = $DEFAULT_SHIP;
    } else {
        $locationID = $DEFAULT_LOCATION;
        $shipID = $DEFAULT_SHIP;
    }
    $shipItemID = mg_create_ship($characterID, $shipID, $locationID);
    mg_fit_item($shipItemID, 3640, 27);
    mg_fit_item($shipItemID, 3651, 28);
    return $shipItemID;
    } else { 
        return FALSE; 
    }
}

function mg_create_session($characterID) {
    mg_dbcatalog();
    
    if (isInMembers($characterID)) {
        //try to find existing session in db
        $mg_session_short = mg_load_short_session_data($characterID);
        
        //if there is one session in db - update it and return session data
        if ($mg_session_short === FALSE) {
            //Need to create basic items
            $_SESSION['mg_character_id'] = $characterID;
            $shipItemID = mg_create_noobship($characterID);
            db_uquery("INSERT INTO `mg_session` VALUES($characterID, $shipItemID, NOW(), 'online');");
            $mg_session = mg_load_session_data($characterID);
            return($mg_session);
        } else {
            //OK we're fine
            $_SESSION['mg_character_id'] = $characterID;
            
            db_uquery("UPDATE `mg_session` SET `state` = 'online', `timestamp` = NOW() WHERE `characterID` = $characterID");
            $mg_session = mg_load_session_data($characterID);
            return($mg_session);
        }
    } else {
        return FALSE;
    }
}

function mg_destroy_session($characterID) {
    unset($_SESSION['mg_character_id']);
    return db_uquery("UPDATE `mg_session` SET `state` = 'offline' WHERE `characterID` = $characterID");
}

function mg_station_layout() {
    ?>
    
    <div id="canvas-wrap">
        <div id="station-area">
            <canvas id="station-canvas" width="800" height="600"></canvas>
        </div> 
        <div id="station-ui">
            <div id="neocom" class="neocom-color">
                <div class="neocom-button white-translucent"><img src="<?=getUrl()?>ccp_icons/79_64_11.png" style="width: 20px; margin: 6px;"  onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')" alt="Neocom" title="Neocom"/></div>
                <img src="<?=getCharacterPortrait($_SESSION['mg_character_id'], 32)?>" alt="<?=getCharacterName($_SESSION['mg_character_id'])?>" title="<?=getCharacterName($_SESSION['mg_character_id'])?>" /><br/>
                <div class="neocom-button white-translucent" title="Chat" onclick="mg_create_or_toggle('Local')"><img src="<?=getUrl()?>UI/WindowIcons/chatchannel.png" width="30"></div>
                <div class="neocom-button white-translucent" title="Industry" onclick="mg_create_or_toggle('Industry'); mg_industry_get_tasks();"><img src="<?=getUrl()?>UI/WindowIcons/Industry.png" width="30"></div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                <div class="neocom-button white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
            </div>
            <div class="col-3">
                <div id="crimewatch-icons">
                    <div class="crimewatch-icon" style="background: rgba(255,0,0,0.5);"> </div>
                    <div class="crimewatch-icon"> </div>
                </div>
                <div id="location-area">
                    <span id="system-name"></span> <span id="system-security"></span> &lt; <span id="constellation-name"></span> &lt; <span id="region-name"></span><br/>
                    <span id="nearest-object"></span> <br/>
                    <div id="faction-info"></div>
                </div>
                <div id="autopilot-area">
                    <div class="large">Route</div>
                    <span id="autopilot">No Destination</span>
                </div>
            </div>
            <div class="col-6" id="inventory-area">

            </div>
            <div class="col-2 neocom-color" id="station-panel">
                <div id="logout-button"><a href="?id=253&logoff_mg" title="Logout from minigame">x</a></div>
                <div id="station-owner-logo"></div>
               <div id="station-undock-button"><span onclick="alert('Scotty the Docking Manager is currently on sick leave, so there is no one to pull that lever.')"><img src="<?=getUrl()?>ccp_icons/9_64_6.png"/>Undock</span></div>
                <div class="row">
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>  
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>

                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>  
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                    <div class="station-button  white-translucent" onclick="alert('You press the button, but except from an audible click, nothing else seems to have happened.')"> </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function mg_in_space_layout() {
    ?>
    <div id="neocom">
        <img src="<?=getCharacterPortrait($_SESSION['mg_character_id'], 32)?>" alt="" /><br/>
    </div>
    <div id="station-area">
        <div class="col-3">
            <div id="crimewatch-icons">
                
            </div>
            <div id="location-area">
                <span id="system-name"></span> <span id="system-security"></span> &lt; <span id="constellation-name"></span> &lt; <span id="region-name"></span>
            </div>
            <div id="autopilot-area">
                <div class="large">Route</div>
                <span id="autopilot">No Destination</span>
            </div>
        </div>
        <div class="col-6">
            
        </div>
        <div class="col-3" id="overview-panel">
            
        </div>
    </div>
    <?php
}

function mg_gm_teleport($itemID, $stationID) {
    global $LM_EVEDB;
    $l = db_asocquery("SELECT * FROM `$LM_EVEDB`.`staStations` WHERE `stationID`=$stationID;");
    if (is_array($l) && count($l) > 0) {
        $l = $l[0];
        db_uquery("UPDATE `mg_assets` SET `parentItemID` = $stationID, `solarSystemID` = {$l['solarSystemID']}, x={$l['x']}, y={$l['y']}, z={$l['z']} WHERE `itemID` = $itemID;");
        db_uquery("UPDATE `mg_assets` SET `solarSystemID` = {$l['solarSystemID']}, x={$l['x']}, y={$l['y']}, z={$l['z']} WHERE `parentItemID` = $itemID;");
        return TRUE;
    } else {
        return FALSE;
    }
    
}

function mg_chat_local_get_list($solarSystemID) {
    global $LM_EVEDB;
    $ls = db_asocquery("SELECT mgs.`characterID`, apc.name AS `characterName`"
            . "FROM `mg_session` mgs "
            . "JOIN `mg_assets` mga ON mgs.`shipItemId` = mga.`itemID` "
            . "JOIN `apicorpmembers` apc ON mgs.`characterID` = apc.`characterID`"
            . "WHERE mga.`solarSystemID` = $solarSystemID AND mgs.`state` = 'online'"
            . "ORDER BY apc.name");
    return ($ls);
}

function mg_update_offline_sessions($dt) {
    return db_uquery("UPDATE `mg_session` SET state='offline' WHERE `timestamp` <= (NOW() - INTERVAL 15 SECOND)");
}

/**
 * This function does the actual server tick! Dogma updates, position udpates, status updates.
 */
function mg_server_tick() {
    $SERVER_TICK = 5;
    $t = time();
    $dt = $t - getConfigItem('mg_tick');
    if ($dt > $SERVER_TICK && getConfigItem('mg_lock','false') == 'false') {
        setConfigItem('mg_lock','true');
        mg_update_offline_sessions($dt);
        setConfigItem('mg_tick',$t); setConfigItem('mg_lock','false');
    }
}

function mg_local_entry($solarSystemID, $characterID, $text) {
    $timestamp = time();
    return db_uquery("INSERT INTO `mg_localchat` VALUES(DEFAULT,$timestamp,$solarSystemID,$characterID,'$text');");
}

function mg_local_get_messages($solarSystemID, $lastTimestamp) {
    return db_asocquery("SELECT mglc.`timestamp`, mglc.`characterID`, apc.`name` AS `characterName`, mglc.`text` FROM `mg_localchat` mglc "
            . "JOIN `apicorpmembers` apc ON mglc.`characterID` = apc.`characterID` WHERE mglc.`solarSystemID` = $solarSystemID AND `timestamp` > $lastTimestamp;");
}

function mg_get_solarsystemid() {
    
    $s = mg_load_short_session_data($_SESSION['mg_character_id']);
    if (count($s) > 0) {
        return $s['solarSystemID'];
    } else {
        return false;
    }
}
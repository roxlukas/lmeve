<?php

require_once('Route.class.php');

class Killmails extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
    }
    
    public function getKillmails() {
        if ($this->ESI->getDEBUG()) inform(get_class(),"Getting Killmails...");
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
        $killmails = $this->get( $this->ESI->getCorporationID() . "/killmails/recent/" );
        //X-pages support
        if ($this->xpages > 1) {
            for ($i = 2; $i <= $this->xpages; $i++) {
                if ($this->ESI->getDEBUG()) inform(get_class(),"Getting Killmails page $i of $this->xpages...");
                $killmails = array_merge($killmails, $this->get( $this->ESI->getCorporationID() . "/killmails/recent/" . '?page=' . $i));
            }
        }
        return $killmails;
    }
    
    public function denormalize() {
        global $LM_EVEDB;
        $q1 = db_uquery("UPDATE `apikillattackers` aka JOIN `$LM_EVEDB`.`invTypes` itp
        ON aka.`shipTypeID` = itp.`typeID`
        LEFT JOIN `$LM_EVEDB`.`chrFactions` fac
        ON aka.`factionID` = fac.`factionID`
        SET aka.`characterName` = itp.`typeName`
        WHERE aka.`characterID` = 0;");
        $q2 = db_uquery("UPDATE `apikillattackers` aka JOIN `$LM_EVEDB`.`invTypes` itp
        ON aka.`shipTypeID` = itp.`typeID`
        LEFT JOIN `$LM_EVEDB`.`chrFactions` fac
        ON aka.`factionID` = fac.`factionID`
        SET aka.`corporationName` = fac.`factionName`
        WHERE aka.`factionID` != 0;");
        $q3 = db_uquery("UPDATE `apikillattackers` 
        SET `weaponTypeID` = `shipTypeID`
        WHERE `weaponTypeID` = 0;");
        return $q1 && $q2 && $q3;
    }
    
    public function getKillmailDetails($killmail_hash, $killmail_id) {
        if ($this->ESI->getDEBUG()) inform(get_class(),"Getting Killmail Details for $killmail_id...");
        $this->setRoute('/v1/killmails/');
        $this->setCacheInterval(1209600);
        $killmail = $this->get( "$killmail_id/$killmail_hash/" );
        return $killmail;
    }
    
    public function killIDexists($killID) {
        $sql="SELECT COUNT(*) AS `count` FROM `apikills` WHERE `killID`=".$killID.";";
        $ret=db_asocquery($sql);
        $ret=$ret[0]['count'];
        if ($ret>0) return TRUE; else return FALSE;
    }
    
    public function killmailHash($victimCharacterID,$attackerCharacterID,$shipTypeID,$killTime) {
    $unixtime=number_format((strtotime($killTime.' UTC') * 10000000) + 116444736000000000,0,'',''); //'UTC' is there because otherwise strtotime() takes OS default timezone into account
    if ($victimCharacterID==0) $victimCharacterID='None';
    if ($attackerCharacterID==0) $attackerCharacterID='None';
    $str="$victimCharacterID$attackerCharacterID$shipTypeID$unixtime";
    $hash=sha1($str);
    //inform('killmail_hash()',"$str sha1 hash=$hash");
    return $hash;
}
    
    private function insertKillmail($killmail) {
        if ($this->killIDexists($this->v($killmail,'killmail_id',0))) {
            if ($this->ESI->getDEBUG()) inform(get_class(), 'Killmail ' . $this->v($killmail,'killmail_id',0) . ' already exists, skipping.');
            return FALSE; //skip this kill, it's already in DB
        }
        $killmail_id = $this->v($killmail,'killmail_id',0);
        //$killmail_hash = $this->v($km,'killmail_hash',null);
        $killmail_time = $this->v($killmail,'killmail_time',0);
        $solar_system_id = $this->v($killmail,'solar_system_id',0);
        $moon_id = $this->v($killmail,'moon_id',0);
        $war_id = $this->v($killmail,'war_id',0);
        $attackers = $this->v($killmail,'attackers',array());
        $victim = $this->v($killmail,'victim', null);
        
        $character_id = 0;
        $final_blow_id = 0;
        
        //insert into apikills moved to end of function
        
        if (!is_null($victim)) {
            $character_id = $this->v($victim,'character_id',0);
            $corporation_id = $this->v($victim,'corporation_id',0);
            $alliance_id = $this->v($victim,'alliance_id',0);
            $faction_id = $this->v($victim,'faction_id',0);
            $damage_taken = $this->v($victim,'damage_taken',0);
            $ship_type_id = $this->v($victim,'ship_type_id',0);
            $items = $this->v($victim,'items',array());

            $ids = array();
            if ( $corporation_id != 0 ) array_push($ids, $corporation_id);
            if ( $alliance_id != 0 ) array_push($ids, $alliance_id);
            if ( $faction_id != 0 ) array_push($ids, $faction_id);

            $map = $this->ESI->Universe->getNamesForIdsMap($ids);

            try {
                $character_name = $this->ESI->Characters->getCharacterName($character_id);
            } catch (Exception $ex) {
                if ($this->ESI->getDEBUG()) {
                    warning(get_class(), "Cannot get Character Name for this victim:");
                    var_dump($victim);
                }
                $character_name = '';
            }

            db_uquery("INSERT IGNORE INTO `apikillvictims` VALUES(" .
                    $killmail_id . "," . 
                    $character_id . "," . 
                    $this->s($character_name) . "," . 
                    $corporation_id . "," . 
                    $this->s($map[$corporation_id]) . "," . 
                    $alliance_id . "," . 
                    $this->s($map[$alliance_id]) . "," . 
                    $faction_id . "," . 
                    $this->s($map[$faction_id]) . "," . 
                    $damage_taken . "," .
                    $ship_type_id .
                ")");

            if (!empty($items)) {
                foreach ($items as $item) {
                    db_uquery("INSERT IGNORE INTO `apikillitems` VALUES(" .
                            $killmail_id . "," . 
                            $this->v($item,'item_type_id',0) . "," . 
                            $this->v($item,'flag',0) . "," . 
                            $this->v($item,'quantity_dropped',0) . "," . 
                            $this->v($item,'quantity_destroyed',0) . "," . 
                            $this->v($item,'singleton',0) .
                    ")");
                }
            }
        }
        if (!empty($attackers)) {
                foreach ($attackers as $attacker) {
                    $character_id = $this->v($attacker,'character_id',0);
                    $corporation_id = $this->v($attacker,'corporation_id',0);
                    $alliance_id = $this->v($attacker,'alliance_id',0);
                    $faction_id = $this->v($attacker,'faction_id',0);
                    if ($this->v($attacker,'final_blow',FALSE) == FALSE) $final_blow = 0; else $final_blow = 1;

                    $ids = array();
                    if ( $corporation_id != 0 ) array_push($ids, $corporation_id);
                    if ( $alliance_id != 0 ) array_push($ids, $alliance_id);
                    if ( $faction_id != 0 ) array_push($ids, $faction_id);

                    $map = $this->ESI->Universe->getNamesForIdsMap($ids);

                    try {
                        $attacker_name = $this->ESI->Characters->getCharacterName($character_id);
                    } catch (Exception $ex) {
                        if ($this->ESI->getDEBUG()) {
                            warning(get_class(), "Cannot get Character Name for this attacker:");
                            var_dump($attacker);
                        }
                        $attacker_name = '';
                    }

                    db_uquery("INSERT IGNORE INTO `apikillattackers` VALUES(" .
                            $killmail_id . "," . 
                            $character_id . "," . 
                            $this->s($attacker_name) . "," . 
                            $corporation_id . "," . 
                            $this->s($map[$corporation_id]) . "," . 
                            $alliance_id . "," . 
                            $this->s($map[$alliance_id]) . "," . 
                            $faction_id . "," . 
                            $this->s($map[$faction_id]) . "," . 

                            $this->v($attacker,'security_status',0) . "," . 
                            $this->v($attacker,'damage_done',0) . "," . 

                            $final_blow . "," . 
                            $this->v($attacker,'weapon_type_id',0) . "," . 
                            $this->v($attacker,'ship_type_id',0) .
                    ")");
                }
            }
            
            $killmail_hash = $this->killmailHash($character_id, $final_blow_id, $ship_type_id, $killmail_time);
            
            db_uquery("INSERT IGNORE INTO `apikills` VALUES(" .
                $killmail_id . "," . 
                $solar_system_id . "," . 
                $this->d($killmail_time) . "," . 
                $moon_id . "," . 
                $this->s($killmail_hash) . 
                ")");
        return TRUE;
    }
    
    /**
     * Scrape killmails from zkillboard (GUI) - do not use
     * use importZKillboardAPI() instead
     * 
     * @deprecated
     * @param type $zKillboardUrl
     * @return boolean
     */
    public function importZKillboardPage($zKillboardUrl) {
        $found = FALSE;
        inform(get_class(), "importZKillboardPage('$zKillboardUrl')");
        //https://zkillboard.com/character/816121566/page/3/
        if (preg_match('/https\:\/\/zkillboard.com\/character\/(\d+)\/page\/(\d+)\//', $zKillboardUrl, $m)) {
            $url = "https://zkillboard.com/character/{$m[1]}/page/{$m[2]}/";
            if ($this->ESI->getDEBUG()) inform(get_class(), "importZKillboardPage(): retrieving '$url'");
            $zkillPage = file_get_contents($url);
            $lines = preg_split('/[\r\n]+/', $zkillPage);
            foreach ($lines as $line) {
                if (preg_match('/\/kill\/(\d+)\//', $line, $m)) {
                    $this->importZKillboard("https://zkillboard.com/kill/{$m[1]}/");
                    sleep(5);
                    $found = TRUE;
                }
            }
            if ($this->ESI->getDEBUG() && $found === FALSE) warning(get_class(), "importZKillboardPage(): Could not find valid zKillboard killmail urls on '$zKillboardUrl'");
            return FALSE;
        } else {
            if ($this->ESI->getDEBUG()) warning(get_class(), "importZKillboardPage(): '$zKillboardUrl' is not a valid zKillboard character page");
            return FALSE;
        }
    }
    
    /**
     * Scrape killmail from zkillboard (GUI) - do not use
     * use importZKillboardAPI() instead
     * 
     * @deprecated
     * @param type $zKillboardUrl
     * @return boolean
     */
    public function importZKillboard($zKillboardUrl) {
        inform(get_class(), "importZKillboard('$zKillboardUrl')");
        if (preg_match('/https\:\/\/zkillboard.com\/kill\/(\d+)\//', $zKillboardUrl, $m)) {
            $zkillPage = file_get_contents("https://zkillboard.com/kill/{$m[1]}/");
            $lines = preg_split('/[\r\n]+/', $zkillPage);
            foreach ($lines as $line) {
                if (preg_match('/https\:\/\/esi.evetech.net\/(dev|legacy|latest|v1)\/killmails\/(\d+)\/(\w+)\//', $line, $m)) {
                    return $this->insertKillmail($this->getKillmailDetails($m[3], $m[2]));
                }
            }
            if ($this->ESI->getDEBUG()) warning(get_class(), "importZKillboard(): Could not find valid ESI URL within zKillboard killmail page '$zKillboardUrl'");
            return FALSE;
        } else {
            if ($this->ESI->getDEBUG()) warning(get_class(), "importZKillboard(): '$zKillboardUrl' is not a valid zKillboard killmail page");
            return FALSE;
        }
    }
    
    /**
     * Download killmails from zkillboard.com API for specified characterID
     * 
     * @param int $characterID
     * @return boolean
     */
    public function importZKillboardAPI($characterID) {
        inform(get_class(), "importZKillboardAPI('$characterID')");
        if (is_numeric($characterID) && $characterID > 0) {
            $options = array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"GET",
                    'header'=>"User-Agent: " . $this->ESI->getUSER_AGENT() . "\r\n" . 
                              "accept: application/json\r\n" .
                              "Content-Type: application/json\r\n"
                 )
            );

            $url = "https://zkillboard.com/api/kills/characterID/$characterID/";
            $ctx = stream_context_create($options);

            $apidata_json = file_get_contents($url, FALSE, $ctx); 
            $apidata = json_decode($apidata_json);
            
            if (is_array($apidata) && count($apidata) > 0) {
                foreach($apidata as $kill) {
                    $this->insertKillmail($this->getKillmailDetails($kill->zkb->hash, $kill->killmail_id));
                }
                return TRUE;
            } else {
                $msg = "importZKillboardAPI(): no valid information received from zkillboard API";
                if ($this->ESI->getDEBUG()) $msg = $msg . ' apidata=' . $apidata_json;
                warning(get_class(), $msg);
                return FALSE;
            }
        } else {
            warning(get_class(), "importZKillboardAPI(): '$characterID' is not a valid characterID");
            return FALSE;
        }
    }
    
    
    public function importKillmail($EsiUrl) {
        inform(get_class(), "importKillmail('$EsiUrl')");
        if (preg_match('/https\:\/\/esi.evetech.net\/(dev|legacy|latest|v1)\/killmails\/(\d+)\/(\w+)\//', $EsiUrl, $m)) {
            return $this->insertKillmail($this->getKillmailDetails($m[3], $m[2]));
        } else {
            if ($this->ESI->getDEBUG()) warning(get_class(), "importKillmail(): '$EsiUrl' is not a valid ESI killmail URL");
            return FALSE;
        }
    }
    
    public function updateKillmails() {
        //crestindustrysystems
        //solarSystemID 	costIndex 	activityID
        inform(get_class(), 'Updating Killmails...');
        $killmails = $this->getKillmails();
        if ($this->ESI->getDEBUG()) var_dump($killmails);

        if ($this->getStatus()=='fresh') {
            if (count($killmails) > 0) {
                foreach ($killmails as $km) {
                    if ( !is_null($this->v($km,'killmail_hash',null)) && !is_null($this->v($km,'killmail_id',null)) ) {
                        if ($this->killIDexists($this->v($km,'killmail_id',0))) {
                            if ($this->ESI->getDEBUG()) inform(get_class(), 'Killmail ' . $this->v($km,'killmail_id',0) . ' already exists, skipping.');
                            continue; //skip this kill, it's already in DB
                        }
                        $this->insertKillmail($this->getKillmailDetails($this->v($km,'killmail_hash',0), $this->v($km,'killmail_id',0)));
                    } else {
                        warning(get_class(),"Problem getting killmail data for killmail_id=" . $this->v($km,'killmail_id',0) . " km=" . print_r($km, TRUE));
                    }
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
    }
    
    public function update() {
        $this->updateKillmails();
        $this->denormalize();
    }
}
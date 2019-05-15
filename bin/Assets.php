<?php

require_once('Route.class.php');

class Assets extends Route {
    
    private $flag_to_id_map = array();
    private $id_to_flag_map = array();
    
    private function cacheInvFlags() {
        global $LM_EVEDB;
        $data = db_asocquery("SELECT `flagID`, `flagName` FROM `$LM_EVEDB`.`invFlags`;");
        foreach ($data as $row) {
            $this->flag_to_id_map[$row['flagName']] = $row['flagID'];
            $this->id_to_flag_map[$row['flagID']] = $row['flagName'];
        }
    }
    
    public function invFlagToID($flagName) {
        if (key_exists($flagName, $this->flag_to_id_map)) {
            return $this->flag_to_id_map[$flagName];
        } else {
            return 0;
        }
    }
    
    public function idToInvFlag($flagID) {
        if (key_exists($flagID, $this->id_to_flag_map)) {
            return $this->id_to_flag_map[$flagID];
        } else {
            return '';
        }
    }
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v3/corporations/');
        $this->setCacheInterval(3600);
        $this->cacheInvFlags();
    }
    
    public function getCorporationAssets() {
        $this->setRoute('/v3/corporations/');
        $this->setCacheInterval(3600);
        $assets = array();
        $assets = $this->get( $this->ESI->getCorporationID() . '/assets/');
        //X-pages support
        if ($this->xpages > 1) {
            for ($i = 2; $i <= $this->xpages; $i++) {
                if ($this->ESI->getDEBUG()) inform(get_class(),"Getting Assets page $i of $this->xpages...");
                $assets = array_merge($assets, $this->get( $this->ESI->getCorporationID() . '/assets/' . '?page=' . $i));
            }
        }
        return $assets;
    }
    
    public function getAssetNames() {
        inform(get_class(), 'Updating Asset Names...');
        inform(get_class(), 'Getting itemIDs from database...');
        global $LM_EVEDB;
        
        $MAX_IDS = 1000;
        
        $sql="SELECT aa.`itemID` FROM `apiassets` aa "
                . "JOIN `$LM_EVEDB`.`invTypes` it ON aa.`typeID` = it.`typeID` "
                . "JOIN `$LM_EVEDB`.`invGroups` ig ON it.`groupID` = ig.`groupID` "
                . "WHERE ig.`categoryID` IN (2, 6, 46, 65) AND aa.`singleton` = 1 AND aa.`corporationID`=" . $this->ESI->getCorporationID();
        if ($this->ESI->getDEBUG()) inform(get_class(), "SQL='$sql'");
        
        $items = db_asocquery($sql);
        
        $checklist = array();
        $names = array();
        foreach($items as $item) {
            array_push($checklist, $item['itemID']);
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "List of itemIDs: ". json_encode($checklist));
        // contact ESI
        inform(get_class(), 'Getting ' . count($checklist) . ' Asset names from ESI...');
        if (count($checklist) > 0) {
            $this->setRoute('/v1/corporations/' . $this->ESI->getCorporationID() . '/assets/names/');
            $this->setCacheInterval(0);
            if (count($checklist) > 0) {
                for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                    if ($this->ESI->getDEBUG()) inform(get_class(), "Getting page $i");
                    $names = array_merge($names, $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS))));
                }
            }
        }  else {
            return FALSE;
        }
        return $names;
    }
    
    public function getCorporationLocations() {
        inform(get_class(), 'Getting Corporation Locations...');
        inform(get_class(), 'Getting itemIDs from database...');
        global $LM_EVEDB;
        
        $MAX_IDS = 1000;
        
        $sql="SELECT aa.`itemID` FROM `apiassets` aa "
                . "JOIN `$LM_EVEDB`.`invTypes` it ON aa.`typeID` = it.`typeID` "
                . "JOIN `$LM_EVEDB`.`invGroups` ig ON it.`groupID` = ig.`groupID` "
                . "WHERE ig.`categoryID` IN (46,65) AND aa.`singleton` = 1 AND aa.`corporationID`=" . $this->ESI->getCorporationID();
        if ($this->ESI->getDEBUG()) inform(get_class(), "SQL='$sql'");
        
        $items = db_asocquery($sql);
        
        $checklist = array();
        $locations = array();
        foreach($items as $item) {
            array_push($checklist, $item['itemID']);
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "List of itemIDs: ". json_encode($checklist));
        // contact ESI
        inform(get_class(), 'Getting ' . count($checklist) . ' Locations from ESI...');
        if (count($checklist) > 0) {
            $this->setRoute('/v2/corporations/' . $this->ESI->getCorporationID() . '/assets/locations/');
            $this->setCacheInterval(0);
            if (count($checklist) > 0) {
                for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                    if ($this->ESI->getDEBUG()) inform(get_class(), "Getting page $i");
                    $locations = array_merge($locations, $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS))));
                }
            }
        } else {
            return FALSE;
        }
        return $locations;
    }
        
    public function updateAssetNames() {
        $names=$this->getAssetNames();
        
        if (count($names) > 0) {
            foreach ($names as $item) {
                db_uquery("INSERT INTO `apiassetnames` VALUES (" . $this->v($item,'item_id',$i++) . "," . $this->s($this->v($item,'name','')) . ") "
                        . "ON DUPLICATE KEY UPDATE `itemName`=" . $this->s($this->v($item,'name','')) );
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function denormalize() {
        global $LM_EVEDB;
        return db_uquery("UPDATE `apilocations` AS al
            JOIN `apiassetnames` an
            ON al.`itemID` = an.`itemID`
            SET al.`itemName` = an.`itemName`;");
    }
    
    public function updateCorpAssets() {
        inform(get_class(), 'Updating corporation Assets...');
        
        $assets = $this->getCorporationAssets();
        
        // apiassets
        // itemID 	parentItemID 	locationID 	typeID 	quantity 	flag 	singleton 	is_blueprint_copy 	rawQuantity 	corporationID
        if ($this->getStatus()=='fresh') {
            if (count($assets) > 0) {
                inform(get_class(), 'Inserting Assets records...');
                db_uquery("DELETE FROM `apiassets` WHERE `corporationID`=" . $this->ESI->getCorporationID());
                foreach ($assets as $c) {
                    $location_id = $this->v($c,'location_id',0);
                    $location_type = $this->v($c,'location_type','other');
                    $parent_id = 0;
                    if ($location_type == 'other') $parent_id = $location_id;
                    if ($this->v($c,'is_singleton',false) === true) $singleton = 1; else $singleton = 0;
                    if ($singleton == 1) $rawQuantity = -1; else $rawQuantity = 'NULL';
                    if ($this->v($c,'is_blueprint_copy',false) === true) $is_blueprint_copy = 1; else $is_blueprint_copy = 'NULL';
                    $sql="INSERT INTO `apiassets` VALUES (".
                            $this->v($c,'item_id',$i++) . "," .
                            $parent_id .",".
                            $location_id .",".
                            $this->v($c,'type_id',0) . "," .
                            $this->v($c,'quantity',0) .",".
                            $this->invFlagToID($this->v($c,'location_flag',0)) . "," .
                            $singleton .",".
                            $is_blueprint_copy .",".
                            $rawQuantity .",".
                            $this->ESI->getCorporationID() .
                        ");";
                    db_uquery($sql);
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        return TRUE;
    }
    
    public function copyPOCO() {
        $pocos = db_asocquery("SELECT * FROM `apiassets` WHERE `typeID`=2233 AND `corporationID`=" . $this->ESI->getCorporationID());
        
        db_uquery("DELETE FROM `apipocolist` WHERE `corporationID`=" . $this->ESI->getCorporationID());
        
        foreach($pocos as $poco) {
            db_uquery("INSERT INTO `apipocolist` VALUES(" . $poco['itemID']. ", " . $poco['locationID'] . ", " . $this->s($this->ESI->Universe->getSolarSystemName($poco['locationID'])) . ", 0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0," . $this->ESI->getCorporationID() . ")");
        }
    }
    
    public function getCorporationPocos() {
        // /v1/corporations/{corporation_id}/customs_offices/
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(3600);
        $poco = array();
        $poco = $this->get( $this->ESI->getCorporationID() . '/customs_offices/');
        //X-pages support
        if ($this->xpages > 1) {
            for ($i = 2; $i <= $this->xpages; $i++) {
                if ($this->ESI->getDEBUG()) inform(get_class(),"Getting Customs_Offices page $i of $this->xpages...");
                $poco = array_merge($poco, $this->get( $this->ESI->getCorporationID() . '/customs_offices/' . '?page=' . $i));
            }
        }
        return $poco;
    }
    
    public function updateCorporationPocos() {
        inform(get_class(), 'Updating corporation Customs_Offices...');
        
        $poco = $this->getCorporationPocos();
        //var_dump($poco);
        
        // apipocolist
        // itemID 	solarSystemID 	solarSystemName 	reinforceHour 	allowAlliance 	allowStandings 	standingLevel 	
        // taxRateAlliance 	taxRateCorp 	taxRateStandingHigh 	taxRateStandingGood 	taxRateStandingNeutral 	taxRateStandingBad 	
        // taxRateStandingHorrible 	corporationID
        
        /*
         object(stdClass)#24 (14) {
            ["alliance_tax_rate"]=>
            float(0)
            ["allow_access_with_standings"]=>
            bool(true)
            ["allow_alliance_access"]=>
            bool(true)
            ["bad_standing_tax_rate"]=>
            float(0.1)
            ["corporation_tax_rate"]=>
            float(0)
            ["excellent_standing_tax_rate"]=>
            float(0)
            ["good_standing_tax_rate"]=>
            float(0)
            ["neutral_standing_tax_rate"]=>
            float(0.1)
            ["office_id"]=>
            float(1030388723453)
            ["reinforce_exit_end"]=>
            int(1)
            ["reinforce_exit_start"]=>
            int(23)
            ["standing_level"]=>
            string(8) "terrible"
            ["system_id"]=>
            int(31000789)
            ["terrible_standing_tax_rate"]=>
            float(0.1)
          }
         */
        if ($this->getStatus()=='fresh') {
            if (count($poco) > 0) {
                inform(get_class(), 'Inserting Customs_Offices records...');
                db_uquery("DELETE FROM `apipocolist` WHERE `corporationID`=" . $this->ESI->getCorporationID());
                foreach ($poco as $c) {
                    $location_id = $this->v($c,'location_id',0);
                    $location_type = $this->v($c,'location_type','other');
                    if ($this->v($c,'allow_access_with_standings',false) === true) $allow_access_with_standings = 1; else $allow_access_with_standings = 0;
                    if ($this->v($c,'allow_alliance_access',false) === true) $allow_alliance_access = 1; else $allow_alliance_access = 0;
                    $sql="INSERT INTO `apipocolist` VALUES (".
                            $this->v($c,'office_id',$i++) . "," .
                            $this->v($c,'system_id',0) . "," .
                            $this->s($this->ESI->Universe->getSolarSystemName($this->v($c,'system_id',0))) . "," .
                            $this->v($c,'reinforce_exit_start',0) . "," .
                            $allow_alliance_access . "," .
                            $allow_access_with_standings . "," .
                            $this->standingToNumber($this->v($c,'standing_level',0)) . "," .
                            $this->v($c,'alliance_tax_rate',0) . "," .
                            $this->v($c,'corporation_tax_rate',0) . "," .
                            $this->v($c,'excellent_standing_tax_rate',0) . "," .
                            $this->v($c,'good_standing_tax_rate',0) . "," .
                            $this->v($c,'neutral_standing_tax_rate',0) . "," .
                            $this->v($c,'bad_standing_tax_rate',0) . "," .
                            $this->v($c,'terrible_standing_tax_rate',0) . "," .
                            $this->ESI->getCorporationID() .
                        ");";
                    db_uquery($sql);
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        
        return TRUE;
    }
    
    public function updateCorporationLocations() {
        inform(get_class(), 'Updating corporation Locations...');
        
        $locations = $this->getCorporationLocations();
        
        if ($this->getStatus()=='fresh') {
            if (count($locations) > 0) {
                inform(get_class(), 'Inserting Locations records...');
                db_uquery("DELETE FROM `apilocations` WHERE `corporationID`=" . $this->ESI->getCorporationID());
                foreach ($locations as $c) {
                    $location_id = $this->v($c,'location_id',0);
                    $location_type = $this->v($c,'location_type','other');
                    $sql="INSERT INTO `apilocations` VALUES (".
                            $this->v($c,'item_id',$i++) . "," .
                            "''," .
                            $this->v($c->position,'x',0) . "," .
                            $this->v($c->position,'y',0) . "," .
                            $this->v($c->position,'z',0) . "," .
                            $this->ESI->getCorporationID() .
                        ");";
                    db_uquery($sql);
                    $this->denormalize();
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        
        return TRUE;
    }
    
    public function standingToNumber($standing) {
        switch ($standing) {
            case 'bad':
                return -4;
                break;
            case 'excellent':
                return 8;
                break;
            case 'good':
                return 4;
                break;
            case 'neutral':
                return 0;
                break;
            case 'terrible':
                return -8;
                break;
            default:
                return 0;
                break;
        }
    }
    
    public function update() {
        $this->updateCorpAssets();
        $this->updateAssetNames();
        $this->updateCorporationPocos();
        $this->updateCorporationLocations();
    }
    
  
       
}
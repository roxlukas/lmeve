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
        
        $sql="SELECT aa.`itemID` FROM `apiassets` aa "
                . "JOIN `$LM_EVEDB`.`invTypes` it ON aa.`typeID` = it.`typeID` "
                . "JOIN `$LM_EVEDB`.`invGroups` ig ON it.`groupID` = ig.`groupID` "
                . "WHERE ig.`categoryID` IN (2, 6) AND aa.`singleton` = 1 AND aa.`corporationID`=" . $this->ESI->getCorporationID();
        if ($this->ESI->getDEBUG()) inform(get_class(), "SQL='$sql'");
        
        $items = db_asocquery($sql);
        
        $tmp = array();
        $names = FALSE;
        foreach($items as $item) {
            array_push($tmp, $item['itemID']);
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "List of itemIDs: ". json_encode($tmp));
        // contact ESI
        inform(get_class(), 'Getting ' . count($tmp) . ' names from ESI...');
        if (count($tmp) > 0) {
            $this->setRoute('/v1/corporations/' . $this->ESI->getCorporationID() . '/assets/names/');
            $this->setCacheInterval(0);
            $names = $this->post('',json_encode($tmp));
        }
        return $names;
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
    
    
    public function update() {
        $this->updateCorpAssets();
        $this->updateAssetNames();
    }
    
  
       
}
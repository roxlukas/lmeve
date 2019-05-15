<?php

require_once('Route.class.php');

class Universe extends Route {
    
    private $typeNameCache = array();
    private $solarSystemNameCache = array();
    private $solarSystemIDCache = array();
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v3/universe/');
        $this->setCacheInterval(3600);
    }
    
    public function update() {
        return TRUE;
    }
    
    public function getTypeName($typeID) {
        global $LM_EVEDB;
        if (isset($this->typeNameCache[$typeID])) {
            if ($this->ESI->getDEBUG()) inform(get_class(), "getTypeName($typeID) found type name in cache.");
            return $this->typeNameCache[$typeID];
        } else {
            $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`invTypes` WHERE `typeID`=$typeID;");
            if (count($data) > 0) {
                if ($this->ESI->getDEBUG()) inform(get_class(), "getTypeName($typeID) found type name in database.");
                $this->typeNameCache[$typeID] = $data[0]['typeName'];
                return($data[0]['typeName']);
            } else return FALSE;
        }
    }
    
    public function getSolarSystemName($solarSystemID) {
        global $LM_EVEDB;
        if (isset($this->solarSystemNameCache[$solarSystemID])) {
            if ($this->ESI->getDEBUG()) inform(get_class(), "getSolarSystemName($solarSystemID) found system name in cache.");
            return $this->solarSystemNameCache[$solarSystemID];
        } else {
            $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`mapSolarSystems` WHERE `solarSystemID`=$solarSystemID;");
            if (count($data) > 0) {
                if ($this->ESI->getDEBUG()) inform(get_class(), "getSolarSystemName($solarSystemID) found system name in database.");
                $this->solarSystemNameCache[$solarSystemID] = $data[0]['solarSystemName'];
                return($data[0]['solarSystemName']); 
            } else return FALSE;
        }
    }
    
    public function getNamesForIds($id_list) {
        global $LM_EVEDB;
        // filter input data
        $this->setRoute('/v2/universe/names/');
        $this->setCacheInterval(600);
        
        $MAX_IDS = 1000;
            
        $checklist = array();
        $checksde = array();
        $names = array();
        foreach($id_list as $id) {
            if (is_numeric($id) && $id > 69000003 && $id < 2000000000) {
                array_push($checklist, $id);
            } else {
                array_push($checksde, $id);
            }
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): List of itemIDs that will be checked in ESI: ". json_encode($checklist));
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): List of itemIDs that will be checked in SDE: ". json_encode($checksde));
        //get from SDE
        if (is_array($checksde) && count($checksde) > 0) {
            inform(get_class(), 'getNamesForIds(): Getting ' . count($checksde) . ' Universe names from SDE...');
            $sde = db_asocquery("SELECT `itemID` as `id`, `itemName` as `name` FROM `$LM_EVEDB`.`invNames` WHERE `itemID` IN (" . implode(',',$checksde) . ")");
            $names = array_merge($names, $sde);
        }
        // contact ESI
        inform(get_class(), 'getNamesForIds(): Getting ' . count($checklist) . ' Universe names from ESI...');
        // contact ESI
        if (count($checklist) > 0) {
            for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): Getting page $i");
                $chunk = $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS)));
                if ($chunk === FALSE) {
                    warning(get_class(), "getNamesForIds(): Received error when trying to resolve names in BULK. Switching to one-by-one.");
                    for ($j = 0; $j < count($checklist); $j++) {
                        $tinychunk = $this->post('',json_encode(array($checklist[$j])));
                        if ($tinychunk === FALSE) {
                            warning(get_class(), "getNamesForIds(): Received ERROR when trying to resolve name name for ID=" . $checklist[$j]);
                            continue;
                        }
                        $names = array_merge($names, $tinychunk);
                    }
                    break; //escape main loop
                    //ToDo: one by one resolving. Report which IDs don't resolve.
                }
                $names = array_merge($names, $chunk);
            }
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): Returning names: ". json_encode($names));
        return $names;
    }
    
    
    public function getNamesForIdsOld($id_list) {
        // filter input data
        $this->setRoute('/v2/universe/names/');
        $this->setCacheInterval(600);
        
        $MAX_IDS = 1000;
            
        $checklist = array();
        $notcheck = array();
        $names = array();
        foreach($id_list as $id) {
            if (is_numeric($id) && $id > 1000000 && $id < 2000000000) {
                array_push($checklist, $id);
            } else {
                array_push($notcheck, $id);
            }
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): List of itemIDs that WILL be checked: ". json_encode($checklist));
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): List of itemIDs that WON'T be checked: ". json_encode($notcheck));
        // contact ESI
        inform(get_class(), 'getNamesForIds(): Getting ' . count($checklist) . ' Universe names from ESI...');
        // contact ESI
        if (count($checklist) > 0) {
            for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): Getting page $i");
                $chunk = $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS)));
                if ($chunk === FALSE) {
                    warning(get_class(), "getNamesForIds(): Received error when trying to resolve names in BULK. Switching to one-by-one.");
                    for ($j = 0; $j < count($checklist); $j++) {
                        $tinychunk = $this->post('',json_encode(array($checklist[$j])));
                        if ($tinychunk === FALSE) {
                            warning(get_class(), "getNamesForIds(): Received ERROR when trying to resolve name name for ID=" . $checklist[$j]);
                            continue;
                        }
                        $names = array_merge($names, $tinychunk);
                    }
                    break; //escape main loop
                    //ToDo: one by one resolving. Report which IDs don't resolve.
                }
                $names = array_merge($names, $chunk);
            }
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "getNamesForIds(): Returning names: ". json_encode($names));
        return $names;
    }
    
    public function getNamesForIdsMap($id_list) {
        $d = $this->getNamesForIds($id_list);
        $r = FALSE;
        if (count($d) > 0) {
            foreach ($d as $i) {
                $r[$i->id] = $i->name;
            }
        }
        return $r;
    }
    
    public function getStationSolarSystemId($stationID) {
        global $LM_EVEDB;
        if (isset($this->solarSystemIDCache[$stationID])) {
            if ($this->ESI->getDEBUG()) inform(get_class(), "getStationSolarSystemId($stationID) found system ID in cache.");
            return $this->solarSystemIDCache[$stationID];
        } else {
            $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`staStations` WHERE `stationID`=$stationID;");
            if (count($data) > 0) {
                if ($this->ESI->getDEBUG()) inform(get_class(), "getStationSolarSystemId($stationID) found system ID in database.");
                $this->solarSystemIDCache[$stationID] = $data[0]['solarSystemID'];
                return($data[0]['solarSystemID']); 
            } else return 0;
        }
    }
}
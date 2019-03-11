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
        // filter input data
        $this->setRoute('/v2/universe/names/');
        $this->setCacheInterval(0);
        
        $MAX_IDS = 1000;
            
        $checklist = array();
        $names = FALSE;
        foreach($id_list as $id) {
            if (is_numeric($id) && $id > 1000000 && $id < 100000000) {
                array_push($checklist, $id);
            }
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "List of itemIDs: ". json_encode($checklist));
        // contact ESI
        inform(get_class(), 'Getting ' . count($checklist) . ' Universe names from ESI...');
        // contact ESI
        if (count($checklist) > 0) {
            for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                if ($this->ESI->getDEBUG()) inform(get_class(), "Getting page $i");
                $names = array_merge($names, $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS))));
            }
        }
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
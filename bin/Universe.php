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
    
    public function getStationSolarSystemId($stationID) {
        global $LM_EVEDB;
        if (isset($this->solarSystemIDCache[$stationID])) {
            if ($this->ESI->getDEBUG()) inform(get_class(), "getStationSolarSystemId($stationID) found system ID in cache.");
            return $this->solarSystemIDCache[$stationID];
        } else {
            $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`stastations` WHERE `stationID`=$stationID;");
            if (count($data) > 0) {
                if ($this->ESI->getDEBUG()) inform(get_class(), "getStationSolarSystemId($stationID) found system ID in database.");
                $this->solarSystemIDCache[$stationID] = $data[0]['solarSystemID'];
                return($data[0]['solarSystemID']); 
            } else return 0;
        }
    }
}
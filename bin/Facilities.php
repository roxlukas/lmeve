<?php

require_once('Route.class.php');

class Facilities extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/industry/');
        $this->setCacheInterval(3600);
    }
    
    private function denormalize() {
        global $LM_EVEDB;
        return db_uquery("UPDATE `apifacilities` AS af
JOIN `$LM_EVEDB`.`invTypes` itp
ON af.`typeID` = itp.`typeID`
JOIN `$LM_EVEDB`.`mapSolarSystems` mss
ON af.`solarSystemID` = mss.`solarSystemID`
JOIN `$LM_EVEDB`.`mapRegions` mrs
ON mss.`regionID` = mrs.`regionID`
SET af.`typeName`= itp.`typeName`,
af.`solarSystemName` = mss.`solarSystemName`,
af.`regionName` = mrs.`regionName`;
");
    }
    
    public function update_public() {
        inform(get_class(), 'Updating public Facilities...');
        $this->setRoute('/v1/industry/');
        $facs = $this->get('facilities/');
        var_dump($facs);
        if ($this->getStatus()=='fresh') {
            if (count($facs) > 0) {
                foreach ($facs as $fac) {
                    $sql="INSERT IGNORE INTO apifacilities VALUES (".
                        $fac->facility_id . "," .
                        $fac->type_id . "," .
                        "NULL," . //typeName
                        $fac->solar_system_id . "," .
                        "NULL," . //solarSystemName
                        $fac->region_id . "," .
                        "NULL," . //regionName
                        "0.00," .
                        "0.00," .
                        $fac->owner_id .
                        ")" .
                    ";";
                    db_uquery($sql);
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        $this->denormalize();
        return TRUE;
    }
    
    public function update_corpo() {
        inform(get_class(), 'Updating corporation private Facilities...');
        $this->setRoute('/v1/corporations/');
        $facs = $this->get( $this->ESI->getCorporationID() . '/facilities/');
        var_dump($facs);
        if ($this->getStatus()=='fresh') {
            if (count($facs) > 0) {
                foreach ($facs as $fac) {
                    $sql="INSERT IGNORE INTO apifacilities VALUES (".
                        $fac->facility_id . "," .
                        $fac->type_id . "," .
                        "NULL," . //typeName
                        $fac->system_id . "," .
                        "NULL," . //solarSystemName
                        "NULL," . //regionID
                        "NULL," . //regionName
                        "0.00," .
                        "0.00," .
                        $this->ESI->getCorporationID() .
                        ")" .
                    ";";
                    db_uquery($sql);
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        $this->denormalize();
        return TRUE;
    }
    
    public function update() {
        $this->update_public();
        $this->update_corpo();
    }
    
  
       
}
<?php

require_once('Route.class.php');

class Stations extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v2/universe/stations/');
        $this->setCacheInterval(300);
    }
    
    public function getStation($stationID) {
        if (!is_numeric($stationID) && $stationID > 0) throw new Exception("getStation() stationID must be numeric and > 0.");
        inform(get_class(), "Updating Station $stationID...");
        $data = $this->get($stationID . '/');
        if ($this->ESI->getDEBUG()) var_dump($data);
        if (is_object($data) && isset($data->name)) {
            return $data;
        } else {
            $msg = "getStation() Cannot get station information for $stationID using " . $this->getRoute();
            warning(get_class(), $msg);
            throw new Exception($msg);
        }
        return FALSE;
    }
    
    public function update() {
        //$this->getCharacter($this->ESI->getCharacterID() .'/');
    }
    
}
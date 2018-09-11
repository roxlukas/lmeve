<?php

require_once('Route.class.php');

class Markets extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/markets/');
        $this->setCacheInterval(3600);
    }

    public function update() {

    }
    
    public function getHistory($typeID, $regionID = null) {
        if (is_null($regionID)) $regionID = getConfigItem ('indexMarketID', $default);
    }
       
}
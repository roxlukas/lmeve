<?php

require_once('Route.class.php');

class Characters extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v4/characters/');
        $this->setCacheInterval(3600);
    }
    
    public function getCharacter($characterID) {
        if (!is_numeric($characterID) && $characterID > 0) throw new Exception("getCharacter() characterID must be numeric and > 0.");
        inform(get_class(), "Updating Character $characterID...");
        $data = $this->get($characterID . '/');
        if ($this->ESI->getDEBUG()) var_dump($data);
        if (is_object($data) && isset($data->corporation_id)) {
            return $data;
        } else {
            $msg = "getCharacter() Cannot get character information for $characterID using " . $this->getRoute();
            warning(get_class(), $msg);
            throw new Exception($msg);
        }
        return FALSE;
    }
    
    public function update() {
        $this->getCharacter($this->ESI->getCharacterID() .'/');
    }
    
}
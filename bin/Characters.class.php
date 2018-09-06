<?php

require_once('Route.class.php');

class Characters extends Route {
    
    private $characterNameCache = array();
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v4/characters/');
        $this->setCacheInterval(3600);
    }
    
    public function getCharacter($characterID) {
        $this->setRoute('/v4/characters/');
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
    
    private function getCharacterFromDb($characterID) {
        $data = db_asocquery("SELECT * FROM `apicorpmembers` WHERE `characterID`=$characterID;");
        if (count($data) > 0) return($data[0]); else return FALSE;
    }
    
    public function getCharacterName($characterID) {
        if (isset($this->characterNameCache[$characterID])) {
            if ($this->ESI->getDEBUG()) inform(get_class(), "getCharacterName($characterID) found character name in cache.");
            return $this->characterNameCache[$characterID];
        } else {
            $c = $this->getCharacterFromDb($characterID);
            if ($c != FALSE) {
                if ($this->ESI->getDEBUG()) inform(get_class(), "getCharacterName($characterID) found character name in LMeve database.");
                $this->characterNameCache[$characterID] = $c['name'];
                return $c['name'];
            } else {
                $d = $this->getCharacter($characterID);
                if (property_exists($d, 'name')) {
                    if ($this->ESI->getDEBUG()) inform(get_class(), "getCharacterName($characterID) found character name using ESI.");
                    $this->characterNameCache[$characterID] = $d->name;
                    return $d->name; 
                } else {
                    warning(get_class(), "getCharacterName($characterID) character name could not be found.");
                    return FALSE;
                }
            }
        }
    }
    
    public function getAggregateStats($characterID) {
        $this->setRoute('/v2/characters/');
        if (!is_numeric($characterID) && $characterID > 0) throw new Exception("getAggregateStats() characterID must be numeric and > 0.");
        inform(get_class(), "Updating Aggregate Stats for Character $characterID...");
        $data = $this->get($characterID . '/stats/');
        if ($this->ESI->getDEBUG()) var_dump($data);
        if (is_array($data) && is_object($data[0])) {
            return $data;
        } else {
            $msg = "getAggregateStats() Cannot get Aggregate Stats information for $characterID using " . $this->getRoute();
            warning(get_class(), $msg);
            throw new Exception($msg);
        }
        return FALSE;
    }
    
    public function getAggregateStatsYear($characterID, $year) {
        $stats = $this->getAggregateStats($characterID);
        if ($stats != FALSE) {
            // do something
            foreach ($stats as $stat) {
                if (is_object($stat) && property_exists($stat, 'year')) {
                    if ($stat->year == $year) return $stat;
                }
            }
        } else {
            $msg = "getAggregateStatsYear() Cannot get Aggregate Stats information for $characterID in year $year using " . $this->getRoute();
            warning(get_class(), $msg);
            throw new Exception($msg);
        }
        return FALSE;
    }
    
    public function update() {
        $this->getCharacter($this->ESI->getCharacterID() .'/');
    }
    
}
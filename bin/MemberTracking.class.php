<?php

require_once('Route.class.php');

class MemberTracking extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(3600);
    }
    
    public function update() {
        inform(get_class(), 'Updating MemberTracking...');
        $membertracking = $this->get( $this->ESI->getCorporationID() . '/membertracking/');
        //var_dump($membertracking);
        if ($this->getStatus()=='fresh') {
            $sqlrows = array();
            foreach($membertracking as $m) {
                if (is_numeric($m->ship_type_id)) $ship_type_id = $m->ship_type_id; else $ship_type_id = 0;
                $c = $this->ESI->Characters->getCharacter($m->character_id);
                array_push($sqlrows, "(".
                                    $m->character_id . ",".
                                    $this->s($c->name) . ",".
                                    $this->s($m->start_date) . ",".
                                    "0,".
                                    "'',".
                                    "'',".
                                    $this->ESI->getCorporationID() . ",".
                                    $this->s($m->logon_date) . ",".
                                    $this->s($m->logoff_date) . ",".
                                    $m->location_id . ",".
                                    $ship_type_id .
                                    ")");
            }
            db_uquery("DELETE FROM `apicorpmembers` WHERE `corporationID`=" . $this->ESI->getCorporationID() . ";");
            $sql = "INSERT IGNORE INTO `apicorpmembers` VALUES " . implode(',', $sqlrows) . ';';
            return db_uquery($sql);
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
    }
}
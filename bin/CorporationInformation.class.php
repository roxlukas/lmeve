<?php

require_once('Route.class.php');

class CorporationInformation extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v4/corporations/');
        $this->setCacheInterval(3600);
    }
    
    public function update() {
        inform(get_class(), 'Updating CorporationInformation...');
    //first, get corporation_id based on character_id who owns the ESI Token
        try {
            $data = $this->ESI->Characters->getCharacter($this->ESI->getCharacterID());
            $this->ESI->setCorporationID($data->corporation_id);
        } catch (Exception $e) {
            $msg = 'Cannot get corporation_id from ESI Token owner: '. $e->getMessage();
            warning(get_class(), $msg);
            throw new Exception($msg);
        }
    //secondly, get the corporation information
        $corp = $this->get( $this->ESI->getCorporationID() . '/');
        if ($this->ESI->getDEBUG()) var_dump($corp);
    //then, get the CEO name
        $ceo = $this->ESI->Characters->getCharacter($corp->ceo_id);
    //next, get the member limit - note we need /v1/ route for that
        $this->setRoute('/v1/corporations/');
        $member_limit = $this->get( $this->ESI->getCorporationID() . '/members/limit/');
        if (!is_numeric($member_limit)) $member_limit = 0;
    //next, get the Home Station name
        $station = $this->ESI->Stations->getStation($corp->home_station_id);
    //next INSERT into "short corp table" `apicorps`
        $sql="INSERT INTO apicorps VALUES (". $this->ESI->getCorporationID() . "," . 
                $this->s($corp->name) . "," .
                $corp->ceo_id."," .
                $this->s($ceo->name) . ",".
                "NULL," .
                $this->ESI->getTokenID() .    
                ")" .
            "ON DUPLICATE KEY UPDATE " .
                "`corporationName` = " . $this->s($corp->name) . "," .
                "`characterID` = " . $corp->ceo_id . "," .
                "`characterName` = " . $this->s($ceo->name) . "," .
                "`tokenID` = " . $this->ESI->getTokenID() . 
            ";";
	db_uquery($sql);
    //finally, INSERT INTO "long corp table" `apicorpsheet`
        if (!isset($corp->alliance_id)) $corp->alliance_id = 0;
        $sql="INSERT INTO apicorpsheet VALUES (".
                $this->ESI->getCorporationID() . ",".
                $this->s($corp->name) . "," .
                $this->s($corp->ticker) . "," .
                $corp->ceo_id.",".
                $this->s($ceo->name) . "," .
                $corp->home_station_id.",".
                $this->s($station->name) . "," .
                $this->s($corp->description) . "," .
                $this->s($corp->url) . "," .
                $corp->alliance_id . ",".
                floor($corp->tax_rate * 100) . ",".
                $corp->member_count . ",".
                $member_limit . ",".
                $corp->shares . ",".
                "0,".
                "0,".
                "0,".
                "0,".
                "0,".
                "0,".
                "0) ".
            "ON DUPLICATE KEY UPDATE " .
                "`corporationName` = " . $this->s($corp->name) . "," .
                "`ticker` = " . $this->s($corp->ticker) . "," .
                "`ceoID` = " . $corp->ceo_id . "," .
                "`ceoName` = " . $this->s($ceo->name) . "," .
                "`stationID` = " . $corp->home_station_id . ',' .
                "`stationName` = " . $this->s($station->name) . "," .
                "`description` = " . $this->s($corp->description) . "," .
                "`url` = " . $this->s($corp->url) . "," .
                "`allianceID` = " . $corp->alliance_id . ',' .
                "`taxRate` = " . floor($corp->tax_rate * 100) . ',' .
                "`memberCount` = " . $corp->member_count . ',' .
                "`memberLimit` = " . $member_limit . ',' .
                "`shares` = " . $corp->shares . ',' .
                "`graphicId` = 0," .
                "`shape1` = 0," .
                "`shape2` = 0," .
                "`shape3` = 0," .
                "`color1` = 0," .
                "`color2` = 0," .
                "`color3` = 0" .
            ";";
	db_uquery($sql);
    }
    
}

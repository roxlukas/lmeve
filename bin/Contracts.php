<?php

require_once('Route.class.php');

class Contracts extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/contracts/public/');
        $this->setCacheInterval(1800);
    }
    
    public function getCorporationContracts() {
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
        $contracts = $this->get( $this->ESI->getCorporationID() . '/contracts/');
        return $contracts;
    }
    
    public function getCorporationContractItems($contract_id) {
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(3600);
        $contracts = $this->get( $this->ESI->getCorporationID() . '/contracts/' . $contract_id . '/items/');
        return $contracts;
    }
    
    public function updateCorporationContractItems($contract_id) {
        inform(get_class(), "Updating Contract items for contract_id=$contract_id...");
        
        $contracts = $this->getCorporationContractItems($contract_id);
        
        if ($this->getStatus()=='fresh') {
            if (count($contracts) > 0) {
                foreach ($contracts as $c) {
                    if ($this->v($o,'is_included',false) === true) $is_included = 1; else $is_included = 0;
                    if ($this->v($o,'is_singleton',false) === true) $is_singleton = 1; else $is_singleton = 0;
                    $sql="INSERT IGNORE INTO `apicontractitems` VALUES (".
                            $contract_id .",".
                            $this->v($c,'record_id',0) .",".
                            $this->v($c,'type_id',0) .",".
                            $this->v($c,'quantity',0) .",".
                            $this->v($c,'raw_quantity',$this->v($c,'quantity',0)) .",".
                            $is_singleton . "," .
                            $is_included . "," .
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
        return TRUE;
    }
    
    public function updateCorporationContracts() {
        inform(get_class(), 'Updating corporation Contracts...');
        
        $contracts = $this->getCorporationContracts();
        
        if ($this->getStatus()=='fresh') {
            if (count($contracts) > 0) {
                foreach ($contracts as $c) {
                    if ($this->v($c,'for_corporation',false) === true) $for_corporation = 1; else $for_corporation = 0;
                    $sql="INSERT INTO `apicontracts` VALUES (".
                            $this->v($c,'contract_id',$i++) .",".
                            $this->v($c,'issuer_id',0) .",".
                            $this->v($c,'issuer_corporation_id',0) .",".
                            $this->v($c,'assignee_id',0) .",".
                            $this->v($c,'acceptor_id',0) .",".
                            $this->v($c,'start_location_id',0) .",".
                            $this->v($c,'end_location_id',0) .",".
                            $this->s($this->v($c,'type','unknown')) .",".
                            $this->s($this->v($c,'status','unknown')) .",".
                            $this->s($this->v($c,'title','')) .",".
                            $for_corporation .",".
                            $this->s($this->v($c,'availability','')) .",".
                            $this->d($this->v($c,'date_issued','')) .",".
                            $this->d($this->v($c,'date_expired','')) .",".
                            $this->d($this->v($c,'date_accepted','')) .",".
                            $this->v($c,'days_to_complete',0) .",".
                            $this->d($this->v($c,'date_completed','')) .",".
                            $this->v($c,'price',0) .",".
                            $this->v($c,'reward',0) .",".
                            $this->v($c,'collateral',0) .",".
                            $this->v($c,'buyout',0) .",".
                            $this->v($c,'volume',0) .",".
                            $this->ESI->getCorporationID() .
                        ") ON DUPLICATE KEY UPDATE ".
				"dateExpired=" . $this->d($this->v($c,'date_expired','')) .",".
				"dateAccepted=" . $this->d($this->v($c,'date_accepted','')) .",".
				"dateCompleted=" . $this->d($this->v($c,'date_completed','')) .",".
				"acceptorID=" . $this->v($c,'acceptor_id',0) .",".
				"status=" . $this->s($this->v($c,'status','unknown')) .
                    ";";
                    db_uquery($sql);
                    $this->updateCorporationContractItems($c->contract_id);
                    $this->ESI->enforceMonolithRateLimit();
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        return TRUE;
    }
    
    public function update() {
        $this->updateCorporationContracts();
    }
    
  
       
}
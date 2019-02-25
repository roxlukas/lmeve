<?php

require_once('Route.class.php');

class Status extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/status/');
        $this->setCacheInterval(30);
    }
    
    public function getServerStatus() {
        $this->setRoute('/v1/status/');
        $this->setCacheInterval(30);
        $status = $this->get('');
        return $status;
    }
    
    public function updateServerStatus() {
        inform(get_class(), "Updating " . $this->ESI->getDatasource() . " server status...");
        
        $status = $this->getServerStatus();
        
        if ($this->getStatus()=='fresh') {
            //var_dump($status);
            if (is_object($status) && property_exists($status,'players')) {
                    if ($this->v($c,'vip',false) === true) $vip = 1; else $vip = '0';
                    $sql="INSERT INTO esiserverstatus VALUES (".
                            "DEFAULT" . "," .
                            "NOW()" . "," .
                            $this->s($this->ESI->getDatasource()) . "," .
                            $this->v($status,'players') . "," .
                            $this->s($this->v($status,'server_version')) . "," .
                            $this->d($this->v($status,'start_time','')) . "," .
                            $vip .
                        ")" .
                    ";";
                    db_uquery($sql);
                
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
        return TRUE;
    }
    
    
    public function update() {
        $this->updateServerStatus();
    }
    
  
       
}
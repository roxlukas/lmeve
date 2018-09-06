<?php

require_once('Route.class.php');

class IndustryJobs extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
    }
    
    public function update() {
        inform(get_class(), 'Updating IndustryJobs...');
        $jobs = $this->get( $this->ESI->getCorporationID() . '/industry/jobs/?include_completed=true');
        var_dump($jobs);
        if ($this->getStatus()=='fresh') {
            if (count($jobs) > 0) {
                foreach ($jobs as $job) {
                    $this->dbInsert($job);
                }
            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
    }
    
    private function dbInsert($job) {
        /* array(1) {
        [0]=>
        object(stdClass)#2232 (18) {
          ["activity_id"]=>
          int(1)
          ["blueprint_id"]=>
          float(1008221416944)
          ["blueprint_location_id"]=>
          float(1024305274107)
          ["blueprint_type_id"]=>
          int(11294)
          ["cost"]=>
          float(4596)
          ["duration"]=>
          int(2880)
          ["end_date"]=>
          string(20) "2018-09-03T12:49:46Z"
          ["facility_id"]=>
          int(60010447)
          ["installer_id"]=>
          int(816121566)
          ["job_id"]=>
          int(371431381)
          ["licensed_runs"]=>
          int(200)
          ["location_id"]=>
          int(60010447)
          ["output_location_id"]=>
          int(629316018)
          ["probability"]=>
          float(1)
          ["product_type_id"]=>
          int(11293)
          ["runs"]=>
          int(5)
          ["start_date"]=>
          string(20) "2018-09-03T12:01:46Z"
          ["status"]=>
          string(6) "active"
        }
      }
       */
       global $LM_EVEDB;
       $corporationID = $this->ESI->getCorporationID();
       //FIELD TRANSLATION

        if ($this->v($job,'product_type_id',0) != 0) {
            $productTypeID=$this->v($job,'product_type_id',0);
        } else {
            switch($this->v($job,'activity_id',0)) {
                case 1:
                    //inform("IndustryJobs.xml", "Looking up productTypeID");
                    $dbq=db_asocquery("SELECT `productTypeID` FROM `$LM_EVEDB`.`yamlBlueprintProducts` WHERE `blueprintTypeID`=".$this->v($job,'blueprint_type_id',0)." AND `activityID`=1;");
                    $productTypeID=$dbq[0]['productTypeID'];
                    //inform("IndustryJobs.xml", "productTypeID=$productTypeID");
                    break;
                default:
                    $productTypeID=$this->v($job,'blueprint_type_id',0);
                    break;
            }

        }
//// INSERT TO CRIUS TABLE
        $sql="INSERT INTO `apiindustryjobscrius` VALUES (".
        $this->v($job,'job_id',0) .",".
        $this->v($job,'installer_id',0) .",".
        $this->s($this->ESI->Characters->getCharacterName($this->v($job,'installer_id',0))).",". //lookup using Characters
        $this->v($job,'facility_id',0) .",".
        $this->ESI->Universe->getStationSolarSystemId($this->v($job,'location_id',0)) .",".
        $this->s($this->ESI->Universe->getSolarSystemName($this->ESI->Universe->getStationSolarSystemId($this->v($job,'location_id',0)))).",". //lookup using Universe
        $this->v($job,'location_id',0) .",".
        $this->v($job,'activity_id',0) .",".
        $this->v($job,'blueprint_id',0) .",".
        $this->v($job,'blueprint_type_id',0) .",".
        $this->s($this->ESI->Universe->getTypeName($this->v($job,'blueprint_type_id',0))).",". //lookup using Universe
        $this->v($job,'blueprint_location_id',0) .",".
        $this->v($job,'output_location_id',0)  . ",".
        $this->v($job,'runs',0) .",".
        $this->v($job,'cost',0) .",".
        $this->v($job,'teamID',0) .",".
        $this->v($job,'licensed_runs',0) .",".
        $this->v($job,'probability',0) .",".
        $productTypeID.",".
        $this->s($this->ESI->Universe->getTypeName($productTypeID)).",". //lookup using Universe
        $this->s($this->v($job,'status','') ).",".
        $this->v($job,'duration',0) .",".
        $this->s($this->v($job,'start_date','') ).",".
        $this->s($this->v($job,'end_date','') ).",".
        $this->s($this->v($job,'pause_date','') ).",".
        $this->s($this->v($job,'completed_date','') ).",".
        $this->v($job,'completed_character_id',0) .",".
        $this->v($job,'successful_runs',0) .",".
        $corporationID .
        ") ON DUPLICATE KEY UPDATE".
        " status=" . $this->s($this->v($job,'status','') ) .
        ",completedDate=".$this->s($this->v($job,'completed_date',0) ).
        ",completedCharacterID=".$this->v($job,'completed_character_id',0) .
        ",successfulRuns=".$this->v($job,'successful_runs',0) .
        ",productTypeID=".$this->v($job,'product_type_id',0) .
        ",productTypeName=".$this->s($this->ESI->Universe->getTypeName($productTypeID)); //todo Universe
        db_uquery($sql);
        
        
//// INSERT TO COMPATIBILITY TABLE


        switch($this->v($job,'status)','')) {
            case 'active': //in progress
                $completed=0;
                $completedSuccessfully=0;
                $completedStatus=0;
                break;
            case 'delivered': //finished
                $completed=1;
                $completedSuccessfully=0;
                $completedStatus=1;
                break;
            case 'cancelled': //failed
                $completed=1;
                $completedSuccessfully=0;
                $completedStatus=0;
                break;
            case 'delivered': //phoebe
                $completed=1;
                $completedSuccessfully=0;
                $completedStatus=0;
                break;
            default:
                $completed=0;
                $completedSuccessfully=0;
                $completedStatus=0;
        }

        //QUERY
        $sql2="INSERT INTO apiindustryjobs VALUES (".
        $this->v($job,'job_id',0) .",".
        $this->v($job,'facility_id',0) .",".
        $this->v($job,'blueprint_location_id',0) .",".
        $this->v($job,'blueprint_id',0) .",".
        $this->v($job,'blueprint_location_id',0) .",".
        "1,".
        "0,".
        "0,".
        $this->v($job,'licensed_runs',0) .",".
        $this->v($job,'output_location_id',0) .",".
        $this->v($job,'installer_id',0) .",".
        $this->v($job,'runs',0) .",".
        $this->v($job,'licensed_runs',0) .",".
        $this->ESI->Universe->getStationSolarSystemId($this->v($job,'location_id',0)) .",".
        $this->v($job,'blueprint_location_id',0) .",".
        "0,".
        "0,".
        "0,".
        "0,".
        $this->v($job,'blueprint_type_id',0) .",".
        $productTypeID.",".
        "0,".
        "0,".
        $completed.",".
        $completedSuccessfully.",".
        $this->v($job,'successful_runs',0) .",".
        "0,".
        "0,".
        $this->v($job,'activity_id',0) .",".
        $completedStatus.",".
        $this->s($this->v($job,'start_date',0) ).",".
        $this->s($this->v($job,'start_date',0) ).",".
        $this->s($this->v($job,'end_date',0) ).",".
        $this->s($this->v($job,'pause_date',0) ).",".
        $corporationID.
        ") ON DUPLICATE KEY UPDATE".
        " completed=".$completed.
        ",completedSuccessfully=".$completedSuccessfully.
        ",completedStatus=".$completedStatus.
        ",successfulRuns=".$this->v($job,'successful_runs',0) .
        ",outputTypeID=".$productTypeID;
        db_uquery($sql2);
   }
       
       
}
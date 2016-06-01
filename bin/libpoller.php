<?php

$mypath=str_replace('\\','/',dirname(__FILE__));
$mylog=$mypath."/../var/poller.txt";
$httplog=$mypath."/../var/http_errors.txt";
$mylock=$mypath."/../var/poller.lock";
$mycache=$mypath."/../var";
$mytmp=$mypath."/../tmp";

date_default_timezone_set(@date_default_timezone_get());
//set_include_path("$mypath/../include");
include_once("$mypath/../include/log.php");
include_once("$mypath/../include/db.php");
include_once("$mypath/../include/configuration.php");
include_once("$mypath/../include/killboard.php");

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function critical($origin,$error) {
	global $mylog,$mylock;
	$message="[CRITICAL] $origin: $error\n";
	loguj($mylog,$message);
	die($message);	
}

function warning($origin,$errorText) {
	global $mylog;
	$message="[WARNING] $origin: $errorText\n";
	echo($message);
	loguj($mylog,$message);	
}

function inform($origin,$errorText) {
	global $mylog;
	$message="[INFORM] $origin: $errorText\n";
	echo($message);
	loguj($mylog,$message);	
}

function apiCheckStatus($keyID,$fileName) {
//checks if there are ANY entries in apistatus table
	if (db_count("SELECT * FROM `apistatus` WHERE keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are enes
	} else {
		return false; //returns false if there have been no entries
	}
}

function apiCheckWarnings($keyID,$fileName) {
//checks if there are TEMPORARY ERROR entries in apistatus table
	if (db_count("SELECT * FROM `apistatus` WHERE errorCode>=500 AND keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are temporary errors
	} else {
		return false; //returns false if there have been no temporary errors
	}
}

function apiCheckErrors($keyID,$fileName) {
//checks if there are UNRECOVERABLE ERROR entries in apistatus table
    global $MAX_ERRORS;
	if (db_count("SELECT * FROM `apistatus` WHERE errorCode>0 AND errorCode<500 AND errorCount >= $MAX_ERRORS AND keyID='$keyID' AND fileName='$fileName';")>0) {
		return true;  //returns true when there are unrecoverable errors
	} else {
		return false; //returns false if there have been no unrecoverable errors
	}
}

function apiSaveWarning($keyID,$error,$fileName) {
	$attrs=$error->attributes();
	$errorCode=$attrs->code;
	$errorMessage=$error;
	if (!apiCheckStatus($keyID,$fileName)) {
		db_uquery("INSERT INTO `apistatus` VALUES (DEFAULT,'$keyID','$fileName',NOW(),$errorCode,1,'$errorMessage');");
	} else {
                if ($errorCode > 0) {
                    $errcount='errorCount+1';
                } else {
                    $errcount='0';
                }
                if (!(strtolower($errorMessage)==='cached')) {
                    $setdate='date=NOW(),';
                } else {
                    $setdate='';
                }
		db_uquery("UPDATE `apistatus` SET $setdate errorCode=$errorCode, errorCount=$errcount, errorMessage='$errorMessage' WHERE keyID='$keyID' AND fileName='$fileName';");
	}
	if ($errorCode > 0) warning($fileName,"ERROR $errorCode: $errorMessage");
}

function apiSaveOK($keyID,$fileName) {
	if (!apiCheckStatus($keyID,$fileName)) {
		db_uquery("INSERT INTO `apistatus` VALUES (DEFAULT,'$keyID','$fileName',NOW(),0,0,'OK');");
	} else {
		db_uquery("UPDATE `apistatus` SET date=NOW(), errorCode=0, errorCount=0, errorMessage='OK' WHERE keyID='$keyID' AND fileName='$fileName';");
	}
}

function lock_check($lock) {
	if (file_exists($lock)) {
		return true;
	} else {
		return false;	
	}
}

function lock_set($lock) {
	if (!lock_check($lock)) {
		touch($lock);
		return true;
	} else {
		return false;
	}
}

function lock_unset($lock) {
	if (lock_check($lock)) {
		unlink($lock);
		return true;
	} else {
		return false;
	}
} 

function cache_file($url, $cache, $interval) { //DEPRECATED, do not use!
	//if a file got polled @ 12:00:20 and next poller time is 12:15:01, a 15 minute cache timer will not be satisfied
	//thus we cut 20 seconds
	if ($interval>20) $interval=$interval-20;
	
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = file_get_contents($cache);
	} else {
   		$data = file_get_contents($url);
   		if ($data===false) {
			//http errors
   		} else {
   		   	file_put_contents($cache, $data, LOCK_EX);
   		}
	}
}

function get_xml_contents($url, $cache, $interval, $getCachedFile=FALSE) {
	//if a file got polled @ 12:00:20 and next poller time is 12:15:01, a 15 minute cache timer will not be satisfied
	//thus we cut 20 seconds
	if ($interval>20) $interval=$interval-20;
	global $httplog,$USER_AGENT;
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = simplexml_load_file($cache);
		$getCachedFile?$xml_data=$data:$xml_data=new SimpleXMLElement('<?phpxml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="0">Cached</error><cachedUntil></cachedUntil></eveapi>');
	} else {
                 $ctx = stream_context_create(array(
                        'http' => array (
                            'ignore_errors' => TRUE,
                            'method'=>"GET",
                            'header'=>"User-Agent: $USER_AGENT\r\n"
                         )
                    ));
                $data=file_get_contents($url, FALSE, $ctx); 
   		//$data = file_get_contents($url);
   		//if ($data === false) {
                if (!empty($http_response_header)) {
                    $http_parse=explode(' ',$http_response_header[0]);
                    $http_code=$http_parse[1];
                    if ($http_code!=200) {
                            //http errors
                            if (empty($http_code)) $http_code=500;
                            $xml_data=new SimpleXMLElement('<?phpxml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="'.$http_code.'">HTTP ERROR! Return code: '.$http_response_header[0].'</error><cachedUntil></cachedUntil></eveapi>');
                            //additional logging!!
                            loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nHTTP RESPONSE:\r\n${http_response_header[0]}\r\n-------- HTTP RESPONSE BELOW THIS LINE --------\r\n$data\r\n------------- END OF HTTP RESPONSE ------------\r\n");
                    } else {
                            file_put_contents($cache, $data, LOCK_EX);
                            $xml_data = simplexml_load_file( $cache );
                            if ($xml_data === false) {
                                    //parser errors
                                    $xml_data=new SimpleXMLElement('<?phpxml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="500">XML Parser error </error><cachedUntil></cachedUntil></eveapi>');
                            }
                    }
                } else {
                    //network problem?
                    $xml_data=new SimpleXMLElement('<?phpxml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="500">NETWORK PROBLEM!</error><cachedUntil></cachedUntil></eveapi>');
                    loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nNETWORK PROBLEM!\r\n");
                }
   	}
	return $xml_data;
}

class crestError {
    private $errorCode=0;
    private $errorText='';
    public $error='';
   
    public function __toString () {
        return $this->errorText;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }

    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    public function getErrorText() {
        return $this->errorText;
    }

    public function setErrorText($errorText) {
        $this->errorText = $errorText;
    }

    public function __construct($errorCode, $errorText) {
        $this->setErrorCode($errorCode);
        $this->setErrorText($errorText);
        $this->error=$errorText;
    }
    
    public function attributes() {
        $ret = new stdClass();
        $ret->code=$this->getErrorCode();
        return $ret;
    }
}



function get_crest_root($root_crest_url) {
    global $mycache;
    $root=get_crest_contents($root_crest_url,"${mycache}/crest_root.json",0);
    //if (isset($root->userCounts))
    if (isset($root)) {
        apiSaveOK(0,"CREST /");
        return $root;
    } else {
        $xml_data=new SimpleXMLElement('<?phpxml version="1.0" encoding="UTF-8"?><eveapi version="2">  <currentTime></currentTime><error code="404">Cannot get CREST root</error><cachedUntil></cachedUntil></eveapi>');
        apiSaveWarning(0, $xml_data->error, "CREST /");
        return FALSE;
    }
}

function get_crest_contents($url, $cache, $interval) {
        $CREST_RATE_LIMIT=30;
	//if a file got polled @ 12:00:20 and next poller time is 12:15:01, a 15 minute cache timer will not be satisfied
	//thus we cut 20 seconds
	if ($interval>20) $interval=$interval-20;
	global $httplog,$USER_AGENT;
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = file_get_contents($cache);
                $ret=new crestError(0,'Cached');
                return $ret;
	} else {
                 $ctx = stream_context_create(array(
                        'http' => array (
                            'ignore_errors' => TRUE,
                            'method'=>"GET",
                            'header'=>"User-Agent: $USER_AGENT\r\n"
                         )
                    ));
                $data=file_get_contents($url, FALSE, $ctx); 
   		//$data = file_get_contents($url);
   		//if ($data === false) {
                if (!empty($http_response_header)) {
                    $http_parse=explode(' ',$http_response_header[0]);
                    $http_code=$http_parse[1];
                    if ($http_code!=200) {
                            //http errors
                            if (empty($http_code)) {
                                $ret=new crestError($http_code,'HTTP Errors');
                                return $ret;
                            }
                            //additional logging!!
                            loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nHTTP RESPONSE:\r\n${http_response_header[0]}\r\n-------- HTTP RESPONSE BELOW THIS LINE --------\r\n$data\r\n------------- END OF HTTP RESPONSE ------------\r\n");
                    } else {
                            file_put_contents($cache, $data, LOCK_EX);
                    }
                } else {
                    //network problem?
                    loguj($httplog,"\r\nREQUEST URI:\r\n$url\r\nNETWORK PROBLEM!\r\n");
                    $ret=new crestError(500,'Network problems');
                    return $ret;
                }
   	}
	$json_data = json_decode($data);
        if ($interval==0) usleep(1000000/$CREST_RATE_LIMIT); //if interval == 0, make sure to respect rate limits
        return $json_data;
}

function load_apikeys_from_file() { //DEPRECATED
	$handle=fopen($myconfig,"r");
	if ($handle) {
		while (($buffer = fgets($handle, 1024)) !== false) {
			$api_line=explode("\t",$buffer);
			$api_keys[$i]['keyID']=$api_line[0];
			$api_keys[$i]['vCode']=$api_line[1];
		}
		if (!feof($handle)) {
			critical("Main","Error while reading configuration");
		}
		fclose($handle);
	}
	return $api_keys;
}

function ins_string($string) {
	$out="'".addslashes($string)."'";
	return $out;
}

function load_apikeys_from_db() {
	$api_keys=db_asocquery("SELECT keyID,vCode FROM cfgapikeys;");
	return $api_keys;
}

function insertAssets($rowset,$parentID,$locationID,$corporationID) { //$parent=0 - root node
		foreach ($rowset as $row) {
			$attrs=$row->attributes();

			if ($parentID==0)	{ 				//if root node
				$locID=$attrs->locationID;  //then take locationID of the row
				$parID=0;					//set parentID=0
			} else {
				$locID=$locationID; 		//otherwise take locationID of the parent
				$parID=$parentID;			//and set parentID as ID of the parent
			}
			
			if (isset($attrs->rawQuantity)) {
				$rawQuantity=$attrs->rawQuantity;
			} else {
				$rawQuantity='NULL';
			}
			
			$sql="INSERT INTO `apiassets` VALUES(".
			$attrs->itemID.",".
			$parID.",".
			$locID.",".
			$attrs->typeID.",".
			$attrs->quantity.",".
			$attrs->flag.",".
			$attrs->singleton.",".
			$rawQuantity.",".
			$corporationID.
			");";
			db_uquery($sql);
			//echo($attrs->itemID.",".$locID."\r\n");
			if (isset($row->rowset)) {
				//echo("HAS CONTENTS!\r\n");
				insertAssets($row->rowset->row,$attrs->itemID,$locID,$corporationID);
			}
		}
	}
        
        function updateOfficeID($corporationID) {
            // officeID to stationID conversion
            //To convert locationIDs greater than or equal to 66000000 and less than 67000000 to stationIDs from staStations
            // subtract 6000001 from the locationID.
            // 
            //To convert locationIDs greater than or equal to 67000000 and less than 68000000 to stationIDs from ConquerableStationList
            // subtract 6000000 from the locationID.
            $r1=db_uquery("UPDATE `apiassets` SET `locationID`=`locationID`-6000001 WHERE `locationID` BETWEEN 66000000 AND 66014933 AND `corporationID`=$corporationID;");
            $r2=db_uquery("UPDATE `apiassets` SET `locationID`=`locationID`-6000000 WHERE `locationID` BETWEEN 66014934 AND 68000000 AND `corporationID`=$corporationID;");
            return($r1 && $r2);
	}
         
       function criusInsert($attrs,$corporationID) {
           global $LM_EVEDB;
           //FIELD TRANSLATION
                                
                                if ($attrs->productTypeID != 0) {
                                    $productTypeID=$attrs->productTypeID;
                                } else {
                                    switch($attrs->activityID) {
                                        case 1:
                                            //inform("IndustryJobs.xml", "Looking up productTypeID");
                                            $dbq=db_asocquery("SELECT `productTypeID` FROM `$LM_EVEDB`.`yamlBlueprintProducts` WHERE `blueprintTypeID`=".$attrs->blueprintTypeID." AND `activityID`=1;");
                                            $productTypeID=$dbq[0]['productTypeID'];
                                            //inform("IndustryJobs.xml", "productTypeID=$productTypeID");
                                            break;
                                        default:
                                            $productTypeID=$attrs->blueprintTypeID;
                                            break;
                                    }
                                    
                                }
           //// INSERT TO CRIUS TABLE
				$sql="INSERT INTO apiindustryjobscrius VALUES (".
				$attrs->jobID.",".
                                $attrs->installerID.",".
                                ins_string($attrs->installerName).",".
                                $attrs->facilityID.",".
                                $attrs->solarSystemID.",".
                                ins_string($attrs->solarSystemName).",".
                                $attrs->stationID.",".
                                $attrs->activityID.",".
                                $attrs->blueprintID.",".
                                $attrs->blueprintTypeID.",".
                                ins_string($attrs->blueprintTypeName).",".
                                $attrs->blueprintLocationID.",".
                                $attrs->outputLocationID.",".
                                $attrs->runs.",".
                                $attrs->cost.",".
                                $attrs->teamID.",".
                                $attrs->licensedRuns.",".
                                $attrs->probability.",".
                                $productTypeID.",".
                                ins_string($attrs->productTypeName).",".
                                $attrs->status.",".
                                $attrs->timeInSeconds.",".
                                ins_string($attrs->startDate).",".
                                ins_string($attrs->endDate).",".
                                ins_string($attrs->pauseDate).",".
                                ins_string($attrs->completedDate).",".
                                $attrs->completedCharacterID.",".
                                $attrs->successfulRuns.",".
				$corporationID.
				") ON DUPLICATE KEY UPDATE".
				" status=".$attrs->status.
				",completedDate=".ins_string($attrs->completedDate).
				",completedCharacterID=".$attrs->completedCharacterID.
                                ",successfulRuns=".$attrs->successfulRuns.
                                ",productTypeID=".$attrs->productTypeID.
                                ",productTypeName=".ins_string($attrs->productTypeName);
				db_uquery($sql);
//// INSERT TO COMPATIBILITY TABLE
                                
                                
                                switch($attrs->status) {
                                    case 1: //in progress
                                        $completed=0;
                                        $completedSuccessfully=0;
                                        $completedStatus=0;
                                        break;
                                    case 104: //finished
                                        $completed=1;
                                        $completedSuccessfully=0;
                                        $completedStatus=1;
                                        break;
                                    case 105: //failed
                                        $completed=1;
                                        $completedSuccessfully=0;
                                        $completedStatus=0;
                                        break;
                                    case 101: //phoebe
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
				$attrs->jobID.",".
				$attrs->facilityID.",".
				$attrs->blueprintLocationID.",".
				$attrs->blueprintID.",".
				$attrs->blueprintLocationID.",".
				"1,".
				"0,".
				"0,".
				$attrs->licensedRuns.",".
				$attrs->outputLocationID.",".
				$attrs->installerID.",".
				$attrs->runs.",".
				$attrs->licensedRuns.",".
				$attrs->solarSystemID.",".
				$attrs->blueprintLocationID.",".
				"0,".
				"0,".
				"0,".
				"0,".
				$attrs->blueprintTypeID.",".
				$productTypeID.",".
				"0,".
				"0,".
				$completed.",".
				$completedSuccessfully.",".
                                $attrs->successfulRuns.",".
				"0,".
				"0,".
				$attrs->activityID.",".
				$completedStatus.",".
				ins_string($attrs->startDate).",".
				ins_string($attrs->startDate).",".
				ins_string($attrs->endDate).",".
				ins_string($attrs->pauseDate).",".
				$corporationID.
				") ON DUPLICATE KEY UPDATE".
				" completed=".$completed.
				",completedSuccessfully=".$completedSuccessfully.
				",completedStatus=".$completedStatus.
                                ",successfulRuns=".$attrs->successfulRuns.
                                ",outputTypeID=".$productTypeID;
				db_uquery($sql2);
       }

?>
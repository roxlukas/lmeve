<?php
$mypath = str_replace('\\', '/', dirname(__FILE__));
include_once("$mypath/../include/log.php");
include_once("$mypath/../include/db.php");
include_once("$mypath/../include/configuration.php");
include_once("$mypath/../include/killboard.php");

include_once('ESI.class.php');

abstract class Route {
    protected $route;
    protected $params;
    protected $cache_interval = 3600; //default cache interval
    /**
     * Can be one of three values: 'fresh' - denotes fresh data from ESI, 'cached' - means data is from local cache, 'error' - means there was some problem
     * @var String
     */
    protected $status='empty';
    /**
     * Reference to the ESI class instance
     * @var ESI
     */
    protected $ESI;
    protected $data;
    protected $xpages;
    
    public function __construct($esi) {
        $this->ESI = $esi; //existing ESI class instance
    }
    
    protected function constructURL() {
        //inform('constructURL()', "before this->params=" . $this->params . "\r\n");
        if (!preg_match('/\?datasource/', $this->params)) {
            //inform('constructURL()', "preg_match('/\?datasource/', this->params) didn't match \r\n");
            if (preg_match('/\?/', $this->params)) {
                //inform('constructURL()', "preg_match('/\?/', this->params) did match \r\n");
                $this->params = str_replace('?', '?datasource=' . $this->ESI->getDatasource() . '&', $this->params);
                //inform('constructURL()', "new this->params=" . $this->params . "\r\n");
            } else {
                $this->params = $this->params . '?datasource=' . $this->ESI->getDatasource();
                //inform('constructURL()', "new this->params=" . $this->params . "\r\n");
            }
        }
        //inform('constructURL()', "returning\r\n");
        return $this->ESI->getESI_BASEURL() . $this->route . $this->params;
    }
    
    protected function constructCacheFilename() {
        if (!is_null($this->ESI->getTokenID())) $t = $this->ESI->getTokenID(); else $t='0';
        return $this->ESI->getMycache() . '/' . $t . '_' . md5($this->ESI->getTokenID() . $this->constructURL()).'.json';
    }
    
    protected function checkErrors() {
        $MAX_ERRORS = $this->ESI->getMAX_ERRORS();
        return $this->checkStatus("errorCode>0 AND errorCode<500 AND errorCount >= $MAX_ERRORS");
        //checks if there are UNRECOVERABLE ERROR entries in esistatus table
    }

    protected function checkStatus($where='TRUE') {
        $tokenID = $this->ESI->getTokenID();
        $route = $this->getRoute();
        //checks if there are ANY entries in esistatus table
        if (db_count("SELECT * FROM `esistatus` WHERE $where AND tokenID='$tokenID' AND route='$route';")>0) {
                return true;  //returns true when there are enes
        } else {
                return false; //returns false if there have been no entries
        }
    }
    
    /**
     * Prepare string for db insertion. Escape characters inside $string and add "'" around it.
     * @param String $string string to be treated for DB insertion
     */
    protected function s($string) {
        return "'" . addslashes($string) . "'";
    }
    
    protected function saveError($errorCode, $data) {
        $tokenID = $this->ESI->getTokenID();
        $route = $this->route;
        
        if (json_decode($data) != FALSE) {
            if (property_exists($data, 'error')) {
                $errorMessage = $data->error;
            } else {
                $errorMessage = $data;
            }
        } else {
            $errorMessage = $data;
        }
        
        $this->saveStatus($errorCode,$errorMessage,TRUE);
        warning($this->route,"ERROR $errorCode: $errorMessage");
    }
    
    protected function saveOK() {
        $this->saveStatus(200,'OK');
    }
    
    /**
     * Get property or provide default value
     * @param type $class - object to get property from
     * @param type $property - property to get
     * @param type $default - default value in case property is not set
     * @return type
     */
    protected function v($class, $property, $default='') {
        if (property_exists($class, $property))
            return $class->$property;
        else
            return $default;
    }

    private function saveStatus($errorCode, $errorMessage, $increaseErrorCount = FALSE) {
        $tokenID = $this->ESI->getTokenID();
        $route = $this->route;
        $errorMessage = addslashes($errorMessage);
        
        if (!$this->checkStatus()) {
            if ($increaseErrorCount) $ecc=1; else $ecc=0;
            db_uquery("INSERT INTO `esistatus` VALUES (DEFAULT,'$tokenID','$route',NOW(),$errorCode,$ecc,'$errorMessage');");
        } else {
            if ($increaseErrorCount) $ecc='errorCount+1'; else $ecc='0';
            db_uquery("UPDATE `esistatus` SET date=NOW(), errorCode=$errorCode, errorCount=$ecc, errorMessage='$errorMessage' WHERE tokenID='$tokenID' AND route='$route';");
        }
    }
    
    private function request($type='GET', $postdata = null) {
        //ToDo: add X-pages support
        $cache = $this->constructCacheFilename();
        if ($this->ESI->getDEBUG()) {
            inform('Route', "New Route->request($type)");
            inform('Route', "constructURL()='" . $this->constructURL());
            inform('Route', "constructCacheFilename()='" . $this->constructCacheFilename());
        }
	if (file_exists($cache) && (filemtime($cache)>(time() - $this->cache_interval ))) {
   		$data = file_get_contents($cache);
                //$ret=new crestError(0,'Cached');
                $this->status='cached';
                $this->data = $data;
	} else {
            if (!$this->checkErrors()) {
                $options = array(
                            'http' => array (
                                'ignore_errors' => TRUE,
                                'method'=>"GET",
                                'header'=>"User-Agent: " . $this->ESI->getUSER_AGENT() . "\r\n" . 
                                          "accept: application/json\r\n" .
                                          "Content-Type: application/json\r\n" .
                                          "Authorization: Bearer " . $this->ESI->getAccessToken()
                             )
                        );
                //if ($this->ESI->getDEBUG()) var_dump($options);
                if ($type == "POST") {
                    $options['http']['method'] = "POST";
                    $options['http']['content'] = $postdata;
                }
                $url = $this->constructURL();
                $ctx = stream_context_create($options);
                $data = file_get_contents($url, FALSE, $ctx); 

                if (!empty($http_response_header)) {
                    $this->xHeadersHandler($http_response_header);
                    $http_code = $this->httpResponseCodeHandler($http_response_header);
                    if ($http_code!=200) {
                        //NOK
                            //ESI will give http errors when there is any type of error
                            if (empty($http_code)) {
                                 //weird response from HTTP server, handle error
                                $this->saveError(1001, $data);
                            } else {
                                //handle error
                                $this->saveError($http_code, $data);
                            }
                            //Stop poller in case of 504 - Tranquility is down, stop barraging the ESI API!
                            if ($http_code == 504) {
                                $msg = "Game server is down, shutting down ESI client. Reason: " . $data;
                                warning(get_class(), $msg);
                                throw new Exception($msg);
                            }
                            //additional logging!!
                            loguj($this->ESI->getHttplog(),"\r\nREQUEST URI:\r\n$url\r\nHTTP RESPONSE:\r\n${http_response_header[0]}\r\n-------- HTTP RESPONSE BELOW THIS LINE --------\r\n$data\r\n------------- END OF HTTP RESPONSE ------------\r\n");
                            $this->status='error';
                            return FALSE;
                    } else {
                        //OK - save data to cache
                        $this->status='fresh';
                        file_put_contents($cache, $data, LOCK_EX);
                    }
                } else {
                    //network problem?
                    loguj($this->ESI->getHttplog(),"\r\nREQUEST URI:\r\n$url\r\nNETWORK PROBLEM!\r\n");
                    //handle network errors - this will happen if there was some network problem (below layer 7)
                    $this->saveError(1002,"Network problem accessing $url");
                    $this->status='error';
                    return FALSE;
                }
                $this->data = $data;
            } else {
                warning(get_class(),"Too many errors when accessing Route $this->route, skipping this route.");
                return FALSE;
            }
   	}
        
	$json_data = json_decode($data);
        //if ($interval==0) usleep(1000000/$CREST_RATE_LIMIT); //if interval == 0, make sure to respect rate limits
        $this->saveOK();
        if ($this->ESI->getDEBUG()) echo("\r\n");
        return $json_data;
    }
    
    public function get($params = '') {
        $this->params = $params;
        return $this->request();
    }
    
    public function post($params, $postdata) {
        $this->params = $params;
        return $this->request('POST',$postdata);
    }
    
    public function getRoute() {
        return $this->route;
    }

    public function getParams() {
        return $this->params;
    }

    public function getCacheInterval() {
        return $this->cache_interval;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getData() {
        return $this->data;
    }

    public function setRoute($route) {
        $this->route = $route;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function setCacheInterval($cache_interval) {
        $this->cache_interval = $cache_interval;
    }
    
    public function getXpages() {
        return $this->xpages;
    }
    
    public function setXpages($xpages) {
        $this->xpages = $xpages;
    }
    
    protected function httpResponseCodeHandler($http_response_header) {
        if (isset($http_response_header) && is_array($http_response_header) && count($http_response_header) > 0 ) {
            foreach ($http_response_header as $header) {
                if ($this->ESI->getDEBUG()) echo($header);
                if (preg_match('/(HTTP)\/([0-9\.]+)\s+(\d+)\s+(.+)/', $header, $m)) {
                    return $m[3];
                    if ($this->ESI->getDEBUG()) echo("Found HTTP/${m[3]}\r\n");
                }
            }
        }
        return FALSE;
    }
     
    protected function xHeadersHandler($http_response_header) {
        //var_dump($http_response_header);
        if (isset($http_response_header) && is_array($http_response_header) && count($http_response_header) > 0 ) {
            foreach ($http_response_header as $header) {
                if (preg_match('/(X-pages): (\d+)/', $header, $m)) {
                    $this->setXpages($m[2]);
                } else if (preg_match('/(X-Esi-Error-Limit-Remain): (\d+)/', $header, $m)) {
                    $this->ESI->setXEsiErrorLimitRemain($m[2]);
                } else if (preg_match('/(X-Esi-Error-Limit-Reset): (\d+)/', $header, $m)) {
                    $this->ESI->setXEsiErrorLimitReset($m[2]);
                }
            }
        }
        /*
         * After reading the comments here and looking at various RFCs, I've decided they all suck and we should go the route of adding a X-Pages header.
         * I don't like Link headers because I don't want to recreate urls w/ parameters there, and there's a slippery slope of supporting not just rel=last,
         * but also first, next, previous... which would be a whole pile of useless junk in 99% of use cases.
         * if you hate the idea of X-Pages now's the time to speak up.
         *
         * otherwise the flow generally would be:
         *
         * - request some paginated endpoint
         * - check if the X-pages response header is more than 1
         * - spin up X-pages - 1 threads to get remainder
         */
    }

        
    /**
     * This function should implement: setup route and parameters, do the actual data retrieval (get(), post()) and save results into database.
     */
    public abstract function update();
}


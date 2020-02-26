<?php

require_once('Route.class.php');

class Usage extends Route {
    
    private $deltaTime = 604800;
    private $url = "https://pozniak.pl/lmeve/api.php?endpoint=STATS";
    
    public function __construct($esi) {
        parent::__construct($esi);
    }
    
    private function getLoginCount($days = 1) {
        if (!is_numeric($days)) return FALSE;
        $q = db_asocquery("SELECT COUNT(*) AS count
        FROM `lmusers`
        WHERE str_to_date(last, '%d.%m.%Y %H:%i') > DATE_SUB(NOW(), INTERVAL $days DAY);
        ");
                
        return $q[0]['count'];
    }
    
    public function sendUsageStats() {
        inform(get_class(), "sendUsageStats() start");
        if (getConfigItem('usageStats','enabled')=='enabled') {
            inform(get_class(), "sendUsageStats() usageStats is enabled");
            if (getConfigItem('usageLastSent', 0) + $this->deltaTime < time()) {
                
                setConfigItem('usageLastSent', time());
                
                $seven = $this->getLoginCount(7);
                $thirty = $this->getLoginCount(30);
                
                $options = array(
                            'http' => array (
                                'ignore_errors' => TRUE,
                                'method'=>"GET",
                                'header'=>"User-Agent: " . $this->ESI->getUSER_AGENT() . "\r\n" . 
                                          "accept: application/json\r\n" .
                                          "Content-Type: application/json\r\n"
                             )
                        );
                
                $url = $this->url . '&sevenDayStats=' . $seven . '&thirtyDayStats=' . $thirty;
                
                inform(get_class(), "sendUsageStats() sending usage data to $url");
                $ctx = stream_context_create($options);
                $data = file_get_contents($url, FALSE, $ctx);
                
                if ($data === false) {
                    warning(get_class(), "sendUsageStats() error sending usage stats: " . json_encode($http_response_header));
                    return;
                }
                
                inform(get_class(), "sendUsageStats() server response: $data");
            }
        } else {
            inform(get_class(), "sendUsageStats() usageStats is disabled");
        }
        inform(get_class(), "sendUsageStats() end");
        return TRUE;
    }
    
    public function update() {
        //$this->getCharacter($this->ESI->getCharacterID() .'/');
    }
    
}
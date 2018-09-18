<?php

require_once('Route.class.php');

class Markets extends Route {
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/markets/');
        $this->setCacheInterval(3600);
    }

    /**
     * Update public market data, common for all corporations in LMeve
     */
    public function updatePublic() {
        $typeids = db_asocquery("SELECT `typeID` FROM `cfgmarket`;");
        if (count($typeids) > 0) {
            foreach ($typeids as $typeid) $this->updateMinMax ($typeid['typeID']);
        }
        $this->updatePrices();
    }
    
    /**
     * Update private corporation market data - Market Orders
     */
    public function update() {
        $this->updateCorporationMarketOrders();
    }
    
    /**
     * Get list of history market orders in region $regionID for type $typeID
     * if regionID is not provided, it will use default from LMeve settings
     * @param int $typeID - id of the type
     * @param int $regionID - id of the region
     * @return array
     */
    public function getHistory($typeID, $regionID = null) {
        $this->setRoute('/v1/markets/');
        $this->setCacheInterval(86400);
        if (is_null($regionID)) $regionID = getConfigItem ('marketRegionID', 10000002);
        return $this->get( $regionID . '/history/?type_id=' . $typeID);
    }
    
    public function getCorporationMarketOrders() {
        $this->setRoute('/v3/corporations/');
        $this->setCacheInterval(1200);
        return $this->get( $this->ESI->getCorporationID() . '/orders/');
    }
    
    public function updateCorporationMarketOrders() {
        $orders = $this->getCorporationMarketOrders();
        if ($this->ESI->getDEBUG) var_dump($orders);
        if ($this->getStatus()=='fresh') {
            if (count($orders) > 0) {
                db_uquery("DELETE FROM `apimarketorders` WHERE `corporationID` = " . $this->ESI->getCorporationID());
                foreach ($orders as $o) {
                    if ($this->v($o,'is_buy_order',false) === true) $bid = 1; else $bid = 0;
                    $sql="INSERT INTO `apimarketorders` VALUES (".
                            $this->v($o,'order_id',$i++) . ',' .
                            $this->v($o,'issued_by',0) . ',' .
                            $this->v($o,'location_id',0) . ',' .
                            $this->v($o,'volume_total',0) . ',' .
                            $this->v($o,'volume_remain',0) . ',' .
                            $this->v($o,'min_volume',1) . ',' .
                            "0," .
                            $this->v($o,'type_id',0) . ',' .
                            $this->s($this->v($o,'range',0)) . ',' .
                            $this->v($o,'wallet_division',0) . ',' .
                            $this->v($o,'duration',0) . ',' .
                            $this->v($o,'escrow',0) . ',' .
                            $this->v($o,'price',0) . ',' .
                            $bid . ',' .
                            $this->s($this->v($o,'issued','')) . ',' .
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
    }
    
    /**
     * Get list of game calculated average and adjusted prices for all items
     * @return array
     */
    public function getPrices() {
        $this->setRoute('/v1/markets/prices/');
        $this->setCacheInterval(3600);
        return $this->get();
    }
    
    public function updatePrices() {
        $d = $this->getPrices();
        if (count($d) > 0) {
            db_uquery("TRUNCATE TABLE crestmarketprices;");
            foreach ($d as $row) {
                if (!isset($row->adjusted_price)) $row->adjusted_price=0.0;
                if (!isset($row->average_price)) $row->average_price=0.0;
                
                $sql="INSERT INTO crestmarketprices VALUES(".
                $row->type_id.",".
                $row->adjusted_price.",".
                $row->average_price.
                ");";
                db_uquery($sql);
            }
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Get list of current market orders in region $regionID for type $typeID
     * if regionID is not provided, it will use default from LMeve settings
     * if systemID is provided, it will filter by specific solarSystem
     * @param int $typeID - id of the type
     * @param int $regionID - id of the region
     * @param int $systemID - id of the solar system
     * @return array
     */
    public function getMarketOrders($typeID, $regionID = null, $systemID = null) {
        $this->setRoute('/v1/markets/');
//        $this->setCacheInterval(300);
        $this->setCacheInterval(3600);
        if (is_null($regionID)) $regionID = getConfigItem ('marketRegionID', 10000002);
        //get data
        $a = $this->get( $regionID . '/orders/?type_id=' . $typeID . '&order_type=all');
        //X-pages support
        if ($this->xpages > 1) {
            for ($i = 2; $i < $this->xpages; $i++) {
                if ($this->ESI->getDEBUG()) inform('Markets',"Getting page $i of $this->xpages...");
                $a = $a + $this->get( $regionID . '/orders/?type_id=' . $typeID . '&order_type=all&page=' . $i);
            }
        }
        if (!is_null($systemID)) {
            $b = array();
            foreach($a as $k => $r) {
                if ($r->system_id == $systemID) $b[$k] = $r;
            }
            $a = $b;
        }
        return $a;
    }
    
    /**
     * Run statistical analysis on order prices
     * @param type $orders Array of orders
     * @return type Array of statistical values
     */
    private function statAnalyze($orders) {
        $N = count($orders);
        /*************** initial set analysis ****************/
        $r['buy']['max'] = null; $r['sell']['max'] = null;
        $r['buy']['min'] = null; $r['sell']['min'] = null;
        $r['buy']['avg'] = 0.0; $r['sell']['avg'] = 0.0;
        $s1_buy = 0.0; $s1_sell = 0.0;
        $s2_buy = 0.0; $s2_sell = 0.0;
        $r['buy']['stddev'] = 0.0; $r['sell']['stddev'] = 0.0;
        $r['buy']['median'] = 0.0; $r['sell']['median'] = 0.0;
        $r['buy']['volume'] = 0; $r['sell']['volume'] = 0;
        $dataset_buy = array(); $dataset_sell = array();
        $Nb = 0; $Ns = 0;

        foreach ($orders as $o) {
            //is this a buy order?
            if ($o->is_buy_order == TRUE) {
                array_push($dataset_buy, $o->price);
                if (is_null($r['buy']['max']) || $o->price > $r['buy']['max']) {
                    $r['buy']['max'] = $o->price;
                }
                if (is_null($r['buy']['min']) || $o->price < $r['buy']['min']) {
                    $r['buy']['min'] = $o->price;
                }
                $s1_buy += $o->price;
                $s2_buy += $o->price ^ 2;
                $r['buy']['volume'] += $o->volume_remain;
                $Nb++;
            } else {
                array_push($dataset_sell, $o->price);
                if (is_null($r['sell']['max']) || $o->price > $r['sell']['max']) {
                    $r['sell']['max'] = $o->price;
                }
                if (is_null($r['sell']['min']) || $o->price < $r['sell']['min']) {
                    $r['sell']['min'] = $o->price;
                }
                $s1_sell += $o->price;
                $s2_sell += $o->price ^ 2;
                $r['sell']['volume'] += $o->volume_remain;
                $Ns++;
            }
        }
        //statistics
        if ($Nb > 0) {
            $r['buy']['stddev'] = sqrt($Nb * $s2_buy - $s1_buy ^ 2) / $Nb;
            $r['buy']['avg'] = $s1_buy / $Nb;
            asort($dataset_buy);
            $r['buy']['median'] = ($dataset_buy[$Nb / 2] + $dataset_buy[$Nb / 2 + 1]) / 2;
        }
        if ($Ns > 0) {
            $r['sell']['stddev'] = sqrt($Ns * $s2_sell - $s1_sell ^ 2) / $Ns;
            $r['sell']['avg'] = $s1_sell / $Ns;
            asort($dataset_sell);
            $r['sell']['median'] = ($dataset_sell[$Ns / 2] + $dataset_sell[$Ns / 2 + 1]) / 2;
        }
        //fix empty values
        if (is_nan($r['buy']['stddev'])) $r['buy']['stddev'] = 0.0;
        if (is_nan($r['sell']['stddev'])) $r['sell']['stddev'] = 0.0;
        if (is_null($r['buy']['max'])) $r['buy']['max'] = 0.0;
        if (is_null($r['sell']['max'])) $r['sell']['max'] = 0.0;
        if (is_null($r['buy']['min'])) $r['buy']['min'] = 0.0;
        if (is_null($r['sell']['min'])) $r['sell']['min'] = 0.0;
        return $r;
    }
    
    /**
     * Remove orders outside of -3 sigma / +3 sigma range
     * @param type $orders Array of orders
     * @param type $med_buy Median buy price
     * @param type $stdd_buy Buy price standard deviation (sigma)
     * @param type $med_sell Median sell price
     * @param type $stdd_sell Sell price standard deviation (sigma)
     * @return array Filtered array of orders
     */
    private function clearOutliers($orders, $med_buy, $stdd_buy, $med_sell, $stdd_sell) {
        $r = array();
        foreach ($orders as $o) {
            //is this a buy order?
            if ($o->is_buy_order == TRUE) {
                //is the price an outlier?
                if (($med_buy - 3 * $stdd_buy < $o->price) && ($o->price < $med_buy + 3 * $stdd_buy)) {
                    array_push($r,$o);
                }
            } else {
                if (($med_sell - 3 * $stdd_sell < $o->price) && ($o->price < $med_sell + 3 * $stdd_sell)) {
                    array_push($r,$o);
                }
            }
        }
        return $r;
    }
    
    private function insertApiprices($typeID, $r) {
        if ($this->ESI->getDEBUG()) echo($typeID . " buy " . $r['buy']['volume'] . ", " . $r['buy']['avg'] . ", " . $r['buy']['max'] . ", " . $r['buy']['min'] . ", " . $r['buy']['stddev'] . ", " . $r['buy']['median'] . ", 0.0)\r\n");
        if ($this->ESI->getDEBUG()) echo($typeID . " sell " . $r['sell']['volume'] . ", " . $r['sell']['avg'] . ", " . $r['sell']['max'] . ", " . $r['sell']['min'] . ", " . $r['sell']['stddev'] . ", " . $r['sell']['median'] . ", 0.0)\r\n");
        $a = db_uquery("DELETE FROM `apiprices` WHERE `typeID` = $typeID");
        $b = db_uquery("INSERT INTO `apiprices` VALUES ($typeID, " . $r['buy']['volume'] . ", " . $r['buy']['avg'] . ", " . $r['buy']['max'] . ", " . $r['buy']['min'] . ", " . $r['buy']['stddev'] . ", " . $r['buy']['median'] . ", 0.0, 'buy')");
        $c = db_uquery("INSERT INTO `apiprices` VALUES ($typeID, " . $r['sell']['volume'] . ", " . $r['sell']['avg'] . ", " . $r['sell']['max'] . ", " . $r['sell']['min'] . ", " . $r['sell']['stddev'] . ", " . $r['sell']['median'] . ", 0.0, 'sell')");

        return $a && $b && $c;
    }
    
    /**
     * Update apiprices for $typeID
     * @param type $typeID - typeID of the item
     * @return boolean TRUE on success, FALSE otherwise
     */
    public function updateMinMax($typeID) {
        if(!is_numeric($typeID)) return FALSE;
        
        $regionID = getConfigItem ('marketRegionID', 10000002);
        $systemID = getConfigItem ('marketSystemID', 30000142);
        $orders = $this->getMarketOrders($typeID, $regionID, $systemID);

        if ($this->getStatus()=='fresh') {
            if (count($orders) > 0) {

                $t = $this->statAnalyze($orders);

                if ($t === FALSE) return FALSE;

                $orders = $this->clearOutliers($orders, $t['buy']['median'], $t['buy']['stddev'], $t['sell']['median'], $t['sell']['stddev']);

                if (count($orders) > 0) {
                    $r = $this->statAnalyze($orders);  
                    if ($r === FALSE) {
                        return $this->insertApiprices($typeID, $t);
                    } else {
                        return $this->insertApiprices($typeID, $r);
                    }
                } else {
                    return $this->insertApiprices($typeID, $t);
                }

            }
        } else {
            inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
            return TRUE;
        }
    }
}
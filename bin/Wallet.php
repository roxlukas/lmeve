<?php

require_once('Route.class.php');

class Wallet extends Route {
    
    // reftype_id map based on https://github.com/esi/eve-glue/blob/master/eve_glue/wallet_journal_ref.py
    
    private $reftype_id = array(
        'undefined' => 0,
        'player_trading' => 1,
        'market_transaction' => 2,
        'gm_cash_transfer' => 3,
        'mission_reward' => 7,
        'clone_activation' => 8,
        'inheritance' => 9,
        'player_donation' => 10,
        'corporation_payment' => 11,
        'docking_fee' => 12,
        'office_rental_fee' => 13,
        'factory_slot_rental_fee' => 14,
        'repair_bill' => 15,
        'bounty' => 16,
        'bounty_prize' => 17,
        'insurance' => 19,
        'mission_expiration' => 20,
        'mission_completion' => 21,
        'shares' => 22,
        'courier_mission_escrow' => 23,
        'mission_cost' => 24,
        'agent_miscellaneous' => 25,
        'lp_store' => 26,
        'agent_location_services' => 27,
        'agent_donation' => 28,
        'agent_security_services' => 29,
        'agent_mission_collateral_paid' => 30,
        'agent_mission_collateral_refunded' => 31,  # pylint: disable=>invalid-name
        'agents_preward' => 32,
        'agent_mission_reward' => 33,
        'agent_mission_time_bonus_reward' => 34,
        'cspa' => 35,
        'cspaofflinerefund' => 36,
        'corporation_account_withdrawal' => 37,
        'corporation_dividend_payment' => 38,
        'corporation_registration_fee' => 39,
        'corporation_logo_change_cost' => 40,
        'release_of_impounded_property' => 41,
        'market_escrow' => 42,
        'agent_services_rendered' => 43,
        'market_fine_paid' => 44,
        'corporation_liquidation' => 45,
        'brokers_fee' => 46,
        'corporation_bulk_payment' => 47,
        'alliance_registration_fee' => 48,
        'war_fee' => 49,
        'alliance_maintainance_fee' => 50,
        'contraband_fine' => 51,
        'clone_transfer' => 52,
        'acceleration_gate_fee' => 53,
        'transaction_tax' => 54,
        'jump_clone_installation_fee' => 55,
        'manufacturing' => 56,
        'researching_technology' => 57,
        'researching_time_productivity' => 58,
        'researching_material_productivity' => 59,  # pylint: disable=invalid-name
        'copying' => 60,
        'reverse_engineering' => 62,
        'contract_auction_bid' => 63,
        'contract_auction_bid_refund' => 64,
        'contract_collateral' => 65,
        'contract_reward_refund' => 66,
        'contract_auction_sold' => 67,
        'contract_reward' => 68,
        'contract_collateral_refund' => 69,
        'contract_collateral_payout' => 70,
        'contract_price' => 71,
        'contract_brokers_fee' => 72,
        'contract_sales_tax' => 73,
        'contract_deposit' => 74,
        'contract_deposit_sales_tax' => 75,
        'contract_auction_bid_corp' => 77,
        'contract_collateral_deposited_corp' => 78,  # pylint: disable=invalid-name
        'contract_price_payment_corp' => 79,
        'contract_brokers_fee_corp' => 80,
        'contract_deposit_corp' => 81,
        'contract_deposit_refund' => 82,
        'contract_reward_deposited' => 83,
        'contract_reward_deposited_corp' => 84,
        'bounty_prizes' => 85,
        'advertisement_listing_fee' => 86,
        'medal_creation' => 87,
        'medal_issued' => 88,
        'dna_modification_fee' => 90,
        'sovereignity_bill' => 91,
        'bounty_prize_corporation_tax' => 92,
        'agent_mission_reward_corporation_tax' => 93,  # pylint: disable=invalid-name
        'agent_mission_time_bonus_reward_corporation_tax' => 94,  # noqa pylint: disable=invalid-name
        'upkeep_adjustment_fee' => 95,
        'planetary_import_tax' => 96,
        'planetary_export_tax' => 97,
        'planetary_construction' => 98,
        'corporate_reward_payout' => 99,
        'bounty_surcharge' => 101,
        'contract_reversal' => 102,
        'corporate_reward_tax' => 103,
        'store_purchase' => 106,
        'store_purchase_refund' => 107,
        'datacore_fee' => 112,
        'war_fee_surrender' => 113,
        'war_ally_contract' => 114,
        'bounty_reimbursement' => 115,
        'kill_right_fee' => 116,
        'security_processing_fee' => 117,
        'industry_job_tax' => 120,
        'infrastructure_hub_maintenance' => 122,
        'asset_safety_recovery_tax' => 123,
        'opportunity_reward' => 124,
        'project_discovery_reward' => 125,
        'project_discovery_tax' => 126,
        'reprocessing_tax' => 127,
        'jump_clone_activation_fee' => 128,
        'operation_bonus' => 129,
        'resource_wars_reward' => 131,
        'duel_wager_escrow' => 132,
        'duel_wager_payment' => 133,
        'duel_wager_refund' => 134,
        'reaction' => 135
    );
    
    public function glueRefTypeToId($ref_type) {
        if (key_exists($ref_type, $this->reftype_id)) {
            return $this->reftype_id[$ref_type];
        } else {
            return 0;
        }
    }
    
    public function __construct($esi) {
        parent::__construct($esi);
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
    }
    
    public function getCorporationWallet() {
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(300);
        $wallet = $this->get( $this->ESI->getCorporationID() . '/wallets/');
        return $wallet;
    }
    
    public function updateCorpWalletBalance() {
        inform(get_class(), 'Updating corporation Wallet Balance...');
        
        $wallets = $this->getCorporationWallet();
        // apiaccountbalance
        // accountID 	accountKey 	balance 	corporationID
        if ($this->getStatus()=='fresh') {
            if (count($wallets) > 0) {
                foreach ($wallets as $c) {
                    $sql="INSERT INTO `apiaccountbalance` VALUES (".
                            $this->ESI->getCorporationID() . '00' . $this->v($c,'division',$i) . "," .
                            $this->v($c,'division',$i++) .",".
                            $this->v($c,'balance',$i++) .",".
                            $this->ESI->getCorporationID() .
                        ") ON DUPLICATE KEY UPDATE ".
				"balance=" . $this->v($c,'balance',$i++) .
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
    
    public function getCorporationWalletJournal($division) {
        // /v3/corporations/{corporation_id}/wallets/{division}/journal/
        $this->setRoute('/v3/corporations/');
        $this->setCacheInterval(3600);
        $journal = $this->get( $this->ESI->getCorporationID() . '/wallets/' . $division . '/journal/');
        return $journal;
    }
    
    public function updateCorpWalletJournal() {
        inform(get_class(), 'Updating corporation Wallet Journal...');
        
        $divisions = $this->ESI->Corporations->getDivisions();
        
        if (property_exists($divisions, 'wallet') && count($divisions->wallet) > 0){
            foreach ($divisions->wallet as $wallet) {
                $journal = $this->getCorporationWalletJournal($wallet->division);
                // apiwalletjournal
                // date 	refID 	refTypeID 	ownerName1 	ownerID1 	ownerName2 	ownerID2 	
                // argName1 	argID1 	amount 	balance 	reason 	corporationID 	accountKey
                if ($this->getStatus()=='fresh') {
                    if (count($journal) > 0) {
                        //grab IDs and resolve to names
                        $ids = array();
                        foreach ($journal as $c) {
                             if ($this->v($c,'first_party_id',0) > 0) $ids[$c->first_party_id] = $c->first_party_id;
                             if ($this->v($c,'second_party_id',0) > 0) $ids[$c->second_party_id] = $c->second_party_id;
                             //if ($this->v($c,'context_id',0)  > 0) $ids[$c->context_id] = $c->context_id;
                        }
                        $names = $this->ESI->Universe->getNamesForIdsMap($ids);
                        //insert data into DB
                        foreach ($journal as $c) {
                            $sql="INSERT IGNORE INTO `apiwalletjournal` VALUES (".
                                    $this->d($this->v($c,'date','')) .",".
                                    $this->v($c,'id',$i++) .",".
                                    $this->glueRefTypeToId($this->v($c,'ref_type','player_trading')) .",".
                                    $this->s($names[$this->v($c,'first_party_id',0)]) . ",".
                                    $this->v($c,'first_party_id',0) .",".
                                    $this->s($names[$this->v($c,'second_party_id',0)]) . ",".
                                    $this->v($c,'second_party_id',0) .",".
                                    $this->s($names[$this->v($c,'context_id',0)]) . ",".
                                    $this->v($c,'context_id',0) .",".
                                    $this->v($c,'amount',0) .",".
                                    $this->v($c,'balance',0) .",".
                                    $this->s($this->v($c,'reason','')) .",".
                                    $this->ESI->getCorporationID() .",".
                                    $wallet->division .
                                ")".
                            ";";
                            if ($this->ESI->getDEBUG()) inform(get_class(), $sql);
                            db_uquery($sql);
                        }
                    }
                } else {
                    inform(get_class(), 'Route ' . $this->getRoute() . $this->getParams() . ' is still cached, skipping...');
                }
            }
        }
        return TRUE;
    }
    
    public function getCorporationWalletTransactions($division) {
        // /v3/corporations/{corporation_id}/wallets/{division}/journal/
        $this->setRoute('/v1/corporations/');
        $this->setCacheInterval(3600);
        $transactions = $this->get( $this->ESI->getCorporationID() . '/wallets/' . $division . '/transactions/');
        return $transactions;
    }
    
    public function updateCorpWalletTransactions() {
        // apiwallettransactions
        // transactionDateTime 	transactionID 	quantity 	typeName 	typeID 	price 	clientID 	
        // clientName 	characterID 	characterName 	stationID 	stationName 	transactionType 	
        // transactionFor 	journalTransactionID 	accountKey 	corporationID

        inform(get_class(), 'Updating corporation Wallet Transactions...');
        
        $divisions = $this->ESI->Corporations->getDivisions();
        
        if (property_exists($divisions, 'wallet') && count($divisions->wallet) > 0){
            foreach ($divisions->wallet as $wallet) {
                $orders = $this->getCorporationWalletTransactions($wallet->division);
                if ($this->ESI->getDEBUG) var_dump($orders);
                if ($this->getStatus()=='fresh') {
                    if (count($orders) > 0) {
                        foreach ($orders as $o) {
                            if ($this->v($o,'is_buy',false) === true) $bid = 'buy'; else $bid = 'sell';
                            $sql="INSERT IGNORE INTO `apiwallettransactions` VALUES (".
                                    $this->d($this->v($o,'date','')) .",".
                                    $this->v($o,'transaction_id',$i++) . ',' .
                                    $this->v($o,'quantity',0) . ',' .
                                    $this->s($this->ESI->Universe->getTypeName($this->v($o,'type_id',0))) . ',' . //type name
                                    $this->v($o,'type_id',0) . ',' .
                                    $this->v($o,'unit_price',0) . ',' .
                                    $this->v($o,'client_id',0) . ',' .
                                    'NULL' . ',' . //client name
                                    '0' . ',' . //character id - not in ESI
                                    'NULL' . ',' . //character name - not in ESI
                                    $this->v($o,'location_id',0) . ',' .
                                    'NULL' . ',' . //station name
                                    $this->s($bid) . "," .
                                    "'corporation'," .
                                    $this->v($o,'journal_ref_id',0) . ',' .
                                    $wallet->division . ',' .
                                    $this->ESI->getCorporationID() .
                                    ");";
                            if ($this->ESI->getDEBUG()) inform(get_class (), $sql);
                            db_uquery($sql);
                        }
                    }
                }
            }
        } else {
            warning(get_class(), 'Cannot read Wallet divisions!');
            return FALSE;
        }
        return TRUE;
    }
    
    public function updateRefTypes() {
        foreach ($this->reftype_id as $k => $v) {
            $t = ucfirst(str_replace('_', ' ', $k));
            $sql="INSERT INTO `apireftypes` VALUES (".
                    "$v" .",".
                    "'$t'" .
                ") ON DUPLICATE KEY UPDATE ".
                    "refTypeName='$t'" . 
            ";";
            db_uquery($sql);
        }
    }
    
    public function update() {
        $this->updateRefTypes();
        $this->updateCorpWalletBalance();
        $this->updateCorpWalletJournal();
        $this->updateCorpWalletTransactions();
    }
    
  
       
}
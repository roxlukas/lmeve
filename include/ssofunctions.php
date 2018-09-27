<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function redirect_to_sso($scope='') {
    global $SSO_AUTH_SERVER, $SSO_CLIENT_ID,$SSO_REDIRECT_URL;
    $SSO_NONCE=token_generate(TRUE);
    header("Location: https://$SSO_AUTH_SERVER/oauth/authorize/?response_type=code&redirect_uri=$SSO_REDIRECT_URL&client_id=$SSO_CLIENT_ID&scope=$scope&state=$SSO_NONCE");
}

function get_sso_state() {
    return str_replace(' ','+',$_GET['state']);
}

function get_login_token($code) {
    global $SSO_AUTH_SERVER, $SSO_CLIENT_ID,$SSO_CLIENT_SECRET;
    $AUTH=base64_encode("$SSO_CLIENT_ID:$SSO_CLIENT_SECRET");
        $postdata = http_build_query(
            array(
                'grant_type' => 'authorization_code',
                'code' => $code
            )
        );
        $CTX_TOKEN = stream_context_create(array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"POST",
                    'header'=>"User-Agent: LMeve/1.0 SSO Client Version/1\r\n".
                        "Authorization: Basic $AUTH\r\n".
                        "Content-Type: application/x-www-form-urlencoded\r\n".
                        "Host: $SSO_AUTH_SERVER",
                    'content' => $postdata
                 )
            ));
        $json_token=file_get_contents("https://$SSO_AUTH_SERVER/oauth/token", FALSE, $CTX_TOKEN);
        $token=json_decode($json_token);
        return $token;
}

function get_access_token($refresh_token) {
   global $SSO_AUTH_SERVER, $SSO_CLIENT_ID,$SSO_CLIENT_SECRET;
    $AUTH=base64_encode("$SSO_CLIENT_ID:$SSO_CLIENT_SECRET");
        $postdata = http_build_query(
            array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token
            )
        );
        $CTX_TOKEN = stream_context_create(array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"POST",
                    'header'=>"User-Agent: LMeve/1.0 SSO Client Version/1\r\n".
                        "Authorization: Basic $AUTH\r\n".
                        "Content-Type: application/x-www-form-urlencoded\r\n".
                        "Host: $SSO_AUTH_SERVER",
                    'content' => $postdata
                 )
            ));
        $json_token = file_get_contents("https://$SSO_AUTH_SERVER/oauth/token", FALSE, $CTX_TOKEN);
        $token=json_decode($json_token);
        
        if (property_exists($token,'refresh_token')) {
            db_uquery("UPDATE `cfgesitoken` SET `token`='$token->refresh_token' WHERE `token` = '$refresh_token';");
        }
        return $token;
        /*
        if (property_exists($token,'access_token')) {
            return $token->refresh_token;
        }
        return FALSE; */
}

function verify_token($login_token) {
    global $SSO_AUTH_SERVER;
    $TOKEN=$login_token->access_token;
        $CTX_VERIFY = stream_context_create(array(
                'http' => array (
                    'ignore_errors' => TRUE,
                    'method'=>"GET",
                    'header'=>"User-Agent: LMeve/1.0 SSO Client Version/1\r\n".
                        "Authorization: Bearer $TOKEN\r\n".
                        "Host: $SSO_AUTH_SERVER"
                 )
            ));
        $json_verify=file_get_contents("https://$SSO_AUTH_SERVER/oauth/verify", FALSE, $CTX_VERIFY);
        $verify=json_decode($json_verify);
        return $verify;
}

function checkOwnerHash($charID,$ownerHash) {
    //when the character logs in for the first time, save the ownerHash and return true
    //when ownerHash exists for the character, compare it. If it's the same, return true, if it's different, return false
    $hash=db_asocquery("SELECT * FROM `lmownerhash` WHERE `characterID`=$charID;");
    if (count($hash)==0) {
        db_uquery("INSERT INTO `lmownerhash` VALUES ($charID,'$ownerHash');");
        return TRUE;
    } else {
        if ($hash[0]['ownerHash']==$ownerHash) return TRUE; else return FALSE;
    }
}

function get_userID($charID) {
    //we ignore ownerHash at the moment, tbd later
    global $USERSTABLE;
        $sql="SELECT lmu.`userID`
                FROM `lmchars` lmc
                JOIN `apicorpmembers` acm
                ON lmc.`charID`=acm.`characterID`
                JOIN `$USERSTABLE` lmu
                ON lmc.`userID`=lmu.`userID`
                WHERE lmc.`charID`=$charID AND lmu.`act`=1;";
        $char=db_query($sql);
        if (count($char)==1) {
            return $char[0][0];
        } else return false;
}

function getLMeveCorpScopes() {
    return("publicData esi-location.read_location.v1 esi-wallet.read_corporation_wallets.v1 esi-search.search_structures.v1 esi-killmails.read_killmails.v1 esi-corporations.read_corporation_membership.v1 esi-corporations.read_structures.v1 esi-killmails.read_corporation_killmails.v1 esi-corporations.track_members.v1 esi-corporations.read_divisions.v1 esi-corporations.read_contacts.v1 esi-assets.read_corporation_assets.v1 esi-corporations.read_blueprints.v1 esi-contracts.read_corporation_contracts.v1 esi-corporations.read_standings.v1 esi-corporations.read_starbases.v1 esi-industry.read_corporation_jobs.v1 esi-markets.read_corporation_orders.v1 esi-corporations.read_container_logs.v1 esi-industry.read_corporation_mining.v1 esi-planets.read_customs_offices.v1 esi-corporations.read_facilities.v1 esi-corporations.read_fw_stats.v1 esi-corporations.read_outposts.v1");
}

/**
 * Compare two space-separated scope strings if they contain exactly identical ESI Scopes
 * @param type $a
 * @param type $b
 */
function compareScopes($a,$b) {
    $a_ = explode(' ',$a);
    $b_ = explode(' ',$b);
    sort($a_);
    sort($b_);
    return $a_ === $b_;
}

function showScopes($a) {
    ?><table class="lmframework"><tr><th>Scopes</th></tr><?php
    $a_ = explode(' ',$a); sort($a_);
    foreach($a_ as $row) {
        ?><tr><td><?=$row?></td></tr><?php
    }
    ?></table><?php
}

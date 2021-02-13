<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Validator {
    static function phone($input) {
        return preg_match('/^(\d{9,9}|\+48\d{9,9})$/',$input);
    }
    
    static function email($input) {
        return preg_match('/^([\-\_\.\w\d]+)\@(.+\.\w+)$/',$input);
    }
    
    static function ipv4($input) {
        return preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/',$input);
    }
    
    static function postCode($input) {
        return preg_match('/^(\d{2,2})\-(\d{3,3})$/',$input);
    }
    
    static function password($input) {
        return preg_match('/[a-z]/',$input) && preg_match('/[A-Z]/',$input) && preg_match('/[0-9]/',$input);
    }
      
    static function nip($input) {
        if(!empty($input)) {
            $weights = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
            $nip = preg_replace('/[\s-]/', '', $input);
            if (strlen($nip) == 10 && is_numeric($nip)) {	 
                $sum = 0;
                for($i = 0; $i < 9; $i++)
                    $sum += $nip[$i] * $weights[$i];
                return ($sum % 11) == $nip[9];
            }
	}
        return false;
    }
    
    static function regon($input) {
        return (Validator::regon9($input) || Validator::regon14($input));
    }
    
    static function regon9($input) {
        if(!empty($input)) {
            $weights = array(8, 9, 2, 3, 4, 5, 6, 7);
            $regon = preg_replace('/[\s-]/', '', $input);
            if (strlen($regon) == 9 && is_numeric($regon)) {	 
                $sum = 0;
                for($i = 0; $i < 8; $i++)
                    $sum += $regon[$i] * $weights[$i];
                return ($sum % 11) == $regon[8];
            }
	}
        return false;
    }
    
    static function regon14($input) {
        if(!empty($input)) {
            $weights = array(2, 4, 8, 5, 0, 9, 7, 3, 6, 1, 2, 4, 8 );
            $regon = preg_replace('/[\s-]/', '', $input);
            if (strlen($regon) == 14 && is_numeric($regon)) {	 
                $sum = 0;
                for($i = 0; $i < 13; $i++)
                    $sum += $regon[$i] * $weights[$i];
                return ($sum % 11) == $regon[13];
            }
	}
        return false;
    }
    
    static function notEmpty($input) {
        if (strlen($input) > 0) { return TRUE; } else { return FALSE; }
    }
    
    static function filterScriptTags($html) {
        $dom = new DOMDocument();
        
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $script = $dom->getElementsByTagName('script');
        $remove = [];
        foreach($script as $item)
        {
          $remove[] = $item;
        }
        foreach ($remove as $item)
        {
          $item->parentNode->removeChild($item); 
        }
        $html = $dom->saveHTML();
        return $html;
    }
    
    static function filterNonAlphanum($input) {
        return preg_replace("/[^a-z0-9_\-.:!?;ąćęłńóśźżĄĆĘŁŃÓŚŹŻ ]/is", "", $input);
    }
}
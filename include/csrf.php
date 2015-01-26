<?php

function random_pseudo_bytes_wrapper($numBytes) {
    if (function_exists('openssl_random_pseudo_bytes') ) {
        //use openssl random pseudo bytes
        return openssl_random_pseudo_bytes($numBytes);
    } else if (function_exists('mt_rand')) {
        //fall back to less secure Mersenne-Twister mt_rand()
        $tmp='';
        for ($i=0; $i < $numBytes; $i++) {
            $tmp.=chr(mt_rand(0, 255));
        }
        return $tmp;
    } else {
        //fall back to least secure php rand()
        $tmp='';
        for ($i=0; $i < $numBytes; $i++) {
            $tmp.=chr(rand(0, 255));
        }
        return $tmp;
    }
}

function token_generate($returnString=FALSE) {
    global $LM_SECUREFORMS,$LM_SECUREFORMSEXPIRY;
    if (!isset($LM_SECUREFORMSEXPIRY)) $LM_SECUREFORMSEXPIRY=300;
    if (!$LM_SECUREFORMS) {
        return TRUE;
    } else {
        $rnd = base64_encode(random_pseudo_bytes_wrapper(64));
        if (!is_array($_SESSION['form-tokens'])) $_SESSION['form-tokens']=array();
        array_push($_SESSION['form-tokens'], array('value'=>$rnd,'valid-until'=>time()+$LM_SECUREFORMSEXPIRY));
        if ($returnString) {
            return $rnd;
        } else {
            echo("<input type=\"hidden\" name=\"form-token\" value=\"$rnd\" />");
        }
        return TRUE;
    }
}

function token_verify($form_token=null) {
    global $LM_SECUREFORMS;
    if (!$LM_SECUREFORMS) {
        return TRUE;
    } else {
        if (is_null($form_token)) $form_token=$_POST['form-token'];
        $timestamp=time();
        if (count($_SESSION['form-tokens'])>0) {
            foreach($_SESSION['form-tokens'] as $key => $token) {
                if ($token['value']===$form_token && $token['valid-until'] > $time) {
                    unset($_SESSION['form-tokens'][$key]);
                    return TRUE;
                }
             }
        } else {
            return FALSE;
        }
    }
    return FALSE;
}

function token_invalidate($form_token) {
    if (count($_SESSION['form-tokens'])>0) {
        foreach($_SESSION['form-tokens'] as $key => $token) {
            if ($token['value']===$form_token) {
                unset($_SESSION['form-tokens'][$key]);
                return TRUE;
            }
        }
    }
    return FALSE;
}

?>

<?php

function token_generate($returnString=FALSE) {
    global $LM_SECUREFORMS,$LM_SECUREFORMSEXPIRY;
    if (!isset($LM_SECUREFORMSEXPIRY)) $LM_SECUREFORMSEXPIRY=300;
    if (!$LM_SECUREFORMS) {
        return TRUE;
    } else {
        $rnd = base64_encode(openssl_random_pseudo_bytes(64));
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

function token_verify() {
    global $LM_SECUREFORMS;
    if (!$LM_SECUREFORMS) {
        return TRUE;
    } else {
        $form_token=$_POST['form-token'];
        $timestamp=time();
        if (count($_SESSION['form-tokens'])>0) {
            foreach($_SESSION['form-tokens'] as $key => $token) {
                if ($token['value']===$form_token && $token['valid-until'] > $time) {
                    unset($_SESSION['form-tokens'][$key]);
                    return TRUE;
                }
             }
        } else {
            //echo('DEBUG: no tokens in session.<br />');
            //var_dump($_SESSION['form-tokens']);
            return FALSE;
        }
    }
    //echo('DEBUG: token not found.');
    //var_dump($_SESSION['form-tokens']);
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

<?php

function token_generate($returnString=FALSE) {
    global $LM_SECUREFORMS,$LM_SESSION;
    if (!$LM_SECUREFORMS) {
        return TRUE;
    } else {
        $length = 48;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rnd = '';
        for ($i = 0; $i < $length; $i++) {
            $rnd .= $characters[rand(0, strlen($characters) - 1)];
        }
        if (!is_array($_SESSION['form-tokens'])) $_SESSION['form-tokens']=array();
        array_push($_SESSION['form-tokens'], array('value'=>$rnd,'valid-until'=>time()+$LM_SESSION));
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

?>

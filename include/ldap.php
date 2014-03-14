<?php
function ldap_auth($user, $password) {
        global $LM_LDAP_USE, $LM_LDAP_HOSTS, $LM_LDAP_DOMAIN, $LM_LDAP_UID;
        
        //are we supposed to use LDAP at all?
        if (!$LM_LDAP_USE) {
                return false;
        }
        
        //find a working LDAP server
        foreach ($LM_LDAP_HOSTS as $server) {
                //echo("Trying: $server... ");
                $ldap = ldap_connect($server);
                ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 2);
                //echo("Binding anonymously... ");
                $testbind = @ldap_bind($ldap);
                
                if ($testbind) {
                        //found a working server!
                        //echo("Binding as $LM_LDAP_UID$user$LM_LDAP_DOMAIN... ");
                        if($bind = @ldap_bind($ldap, $LM_LDAP_UID.$user.$LM_LDAP_DOMAIN, $password)) {
                        //if($bind = @ldap_bind($ldap, $user, $password)) {
                                //and the password is good!!
                                //echo("SUCCESS!");
                                $_SESSION['LOGIN_REALM']='LDAP';
                                ldap_unbind($ldap);
                                return true;
                        } else {
                                //but the password is bad
                                //echo("BAD LDAP PASSWORD! ");
                                return false;
                        }
                }
                //echo("FAILED TO CONNECT TO LDAP! ");
        }
        
        //didn't find a working server
        return false;
}
?>
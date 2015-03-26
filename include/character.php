<?php

function getCharacterInfo($id) {
	global $LM_XML_API_SERVER;
	if (($id)==0) return FALSE;
	$api_url="https://$LM_XML_API_SERVER/eve/CharacterInfo.xml.aspx?characterID=$id";
	//echo("api_url=$api_url<br/>");
	$cache="../var/CharacterInfo_$id.xml";

	if (file_exists($cache) && (filemtime($cache)>(time() - 60*60*24 ))) {
	    $data = file_get_contents($cache);
	} else {
 	    $data = file_get_contents($api_url);
            if ($data===false) {
                //http errors
            } else {
                file_put_contents($cache, $data, LOCK_EX);
            }
	}

        $xml=simplexml_load_string($data);
        $res=$xml->result;
        return $res;
}


?>

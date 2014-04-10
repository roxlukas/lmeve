<?php

// CORS proxy
// https://web.ccpgamescdn.com/ccpwgl/res/   dx9/model/lensflare/orange.red\
//
// use: proxy.php?fetch=dx9/model/lensflare/orange.red
//

$BASEURL='https://web.ccpgamescdn.com/ccpwgl/res/';

function cache_file($url, $cache, $interval) { //unused for now
	if (file_exists($cache) && (filemtime($cache)>(time() - $interval ))) {
   		$data = file_get_contents($cache);
	} else {
   		$data = file_get_contents($url);
   		if ($data===false) {
			return false;
   		} else {
   		   	file_put_contents($cache, $data, LOCK_EX);
   		}
	}
        return $data;
}

$addr=$_GET['fetch'];

//validate!
if ( !preg_match('/^[\w\d\.\/]+$/',$addr) || preg_match('/\.\./',$addr) ) die('Filter error!');

$url = $BASEURL.$addr;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $data = curl_exec($ch);
  $info = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
  
  if ($info=='text/plain; charset=UTF-8') $info='text/xml; charset=UTF-8';
  
  curl_close($ch);

//output!
header("Content-type: $info");
echo $data;

?>
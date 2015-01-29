<?php

// CORS proxy
// https://web.ccpgamescdn.com/ccpwgl/res/   dx9/model/lensflare/orange.red\
//
// use: proxy.php?fetch=dx9/model/lensflare/orange.red
//

include_once('../../config/config.php');

if (!$LM_CCPWGL_USEPROXY) die('Error: proxy is disabled.');
/*
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
 */

$addr=$_GET['fetch'];

//validate!
if ( !preg_match('/^[\w\d\.\/]+$/',$addr) || preg_match('/\.\./',$addr) ) die('Filter error!');

$url = $LM_CCPWGL_URL.$addr;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $data = curl_exec($ch);
  $info = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
  $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
  
  if ($info=='text/plain; charset=UTF-8') $info='text/xml; charset=UTF-8';
  
  curl_close($ch);
  
  //Add CORS header in header so API can be used with web apps on other servers
  header("Access-Control-Allow-Origin: *");
  if ($data===FALSE) {
      //error occured
      $errno=curl_errno($ch);
      switch ($errno) {
          case 78:
              http_response_code(404);
              break;
          case 9:
              http_response_code(403);
              break;
          default:
              http_response_code(404);
              break;
      }
      
  } else {
      //no error
      //output!
      //Add proper content-type header
      header("Content-type: $info");
      //proxy the http code as well
      //http_response_code($http_code);

      echo $data;
  }



?>
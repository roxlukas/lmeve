<?php
/**********************************************************************************
								LM Framework v3
								
	A simple PHP based application framework.
	
	Contact: pozniak.lukasz@gmail.com
	
	Copyright (c) 2005-2013, �ukasz Po�niak
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
	
	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

**********************************************************************************/

include_once(dirname(__FILE__).'/log.php');
include_once(dirname(__FILE__).'/../config/config.php');

$PDO_CONNECTION=null;

function getTypeIDicon($typeID,$size=32) {
    if (!is_numeric($typeID)) $typeID=0;
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=512)) $size=32;
    if ($size != 512) {
        if (file_exists("../wwwroot/ccp_img/${typeID}_${size}.png")) {
            $icon=getUrl()."ccp_img/${typeID}_${size}.png";
        } else {
            $icon="https://imageserver.eveonline.com/Type/${typeID}_${size}.png";
        }
    } else {
        if (file_exists("../wwwroot/ccp_renders/${typeID}.png")) {
            $icon=getUrl()."ccp_renders/${typeID}.png";
        } else {
            $icon="https://imageserver.eveonline.com/Render/${typeID}_${size}.png";
        }
    }
    return($icon);
}

function generate_title($subtitle = null) {
    global $LM_APP_NAME, $lmver;
    $main_title = "$LM_APP_NAME $lmver";
    
    if (is_null($subtitle)) $title="$main_title"; else $title="$main_title - $subtitle";
    return $title;
}

function generate_meta($description=null, $title=null ,$image=null) {
    global $META, $TITLE, $LM_APP_NAME, $lmver;
    
    if (is_null($description)) $description="LMeve: Industry Contribution and Mass Production Tracker.";
    if (is_null($title)) $title = generate_title();
    if (is_null($image)) $image = getUrl() . "ccp_icons/33_128_2.png";
    
    $url = parse_url(getUrl());
    $domain = $url['scheme'] . '://' . $url['host'] ;
    $site = $url['host'];
    
    $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Pragma" CONTENT="content-cache">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="description" content="' . $description . '">
        <meta name="title" content="' . $title . '">
        <meta name="keywords" content="eve-online, eve, ccp, ccp games, lmeve, industry, production, invention, manufacturing, crafting, massively, multiplayer, online, role, playing, game, mmorpg, isk, mmorpg">
        <meta name="robots" content="index,follow">
        <meta name="og:locale" content="en_US">
        <meta name="og:type" content="website">
        <meta name="og:site_name" content="' . $site . '">
        <meta name="fb:app_id" content="">
        <meta name="twitter:site" content="@rox_lukas">
        <meta name="twitter:domain" content="' . $domain . '">
        <meta name="application-name" content="LMeve" />
        <meta name="msapplication-TileColor" content="#1D2C38" />
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="shortcut icon" sizes="16x16" href="' . getUrl() . 'favicon.ico" />
        <meta name="twitter:title" content="' . $title . '">
        <meta name="twitter:image" content="' . $image . '">
        <meta name="twitter:card" content="summary">
        <meta name="og:title" content="' . $title . '">
        <meta name="og:url" content="' . getUrl() . '">
        <meta name="twitter:description" content="' . $description . '">
        <meta name="og:description" content="' . $description . '">
        <meta name="og:image" content="' . $image . '">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            ' . $title . '
        </title>';
    $META = $meta;
    return $meta;
}

function printerr($text) {
	echo("<br><table class=\"error\"><tr><td>$text</td></tr></table>");
	echo('<input type="button" value="&lt; Back" onclick="history.back();">');
}

function secureGETnum($field) {
	$what=$_REQUEST[$field];
	if (!empty($what) && !preg_match('/^(\-){0,1}([\d]+)(\.\d+){0,1}$/',$what)) {
		printerr("Niepoprawny parametr $field.");
		die('');
	}
	return $what;
}

function secureGETstr($field,$len=32768,$http=false) {
	if (!$http) {
		$what=htmlspecialchars($_REQUEST[$field]);
	} else {
		$what=$_GET[$field];
	}
        //if (!get_magic_quotes_gpc()) $what=addslashes($what);
        $what=addslashes($what);
	//echo("DEBUG: $field='$what' (".strlen($what)." of $len)<br>");
	$what=substr($what,0,$len);
	return $what;
}

/*****************************************************************************
Funkcje dost�pu do bazy danych
*****************************************************************************/

//db_connect zwraca identyfikator po��czenia z MySQL
function db_connect() {
    global $LM_DEBUG,$LM_DBENGINE,$LM_dbhost,$LM_dbname,$LM_dbuser,$LM_dbpass,$PDO_CONNECTION;
    
    if (!is_null($PDO_CONNECTION)) return($PDO_CONNECTION);
    
    if ($LM_DBENGINE=="MYSQL") {
        $dsn='mysql';
    } else if ($LM_DBENGINE=="PGSQL") {
        $dsn='pgsql';
    } else {
        die('Error: $LM_DBENGINE setting is missing in config.php');
    }
		
    try {
        $ret = new PDO("$dsn:host=$LM_dbhost;dbname=$LM_dbname;charset=utf8", $LM_dbuser, $LM_dbpass, array(PDO::ATTR_EMULATE_PREPARES => false, 
                                                                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $ret->exec("SET CHARACTER SET utf8");
    } catch(PDOException $ex) {
        if ($LM_DEBUG==1) {
                    printerr("No connection to the database.<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("No connection to the database. Contact your administrator and report the problem.<br/>");
        }
        loguj(dirname(__FILE__).'/../var/error.txt',"Error connecting to the database. MySQL reply: ".$ex->getMessage());
        die();
    }
    $PDO_CONNECTION=$ret;
    return($ret);
    
}

//db_query zwraca dwuwymiarow� tablic� z rekordami
function db_query($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
	
	    try {
                $stmt = $my_link->query($sql); 
                $result = $stmt->fetchAll(PDO::FETCH_NUM);
            } catch(PDOException $ex) {
                loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
                if ($LM_DEBUG==1) {
                        printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
                } else {
                        printerr("Database error. Contact your administrator and report the problem.<br/>");
                }
                die();
            }
        //echo("<pre>db_query($sql): "); var_dump($result); echo('</pre>');
	return($result);
}

//db_asocquery zwraca asocjacyjn� tablic� z rekordami
function db_asocquery($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
	
        try {
            $stmt = $my_link->query($sql); 
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $ex) {
            loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
            if ($LM_DEBUG==1) {
                    printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("Database error. Contact your administrator and report the problem.<br/>");
            }
            die();
        }
	
        //echo("<pre>db_asocquery($sql): "); var_dump($result); echo('</pre>');
	return($result);
}

//db_count zwraca ilo�� rekord�w wybranych przez zapytanie
function db_count($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
        try {
            $stmt = $my_link->query($sql); 
            $rows = count($stmt->fetchAll(PDO::FETCH_NUM));
        } catch(PDOException $ex) {
            loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
            if ($LM_DEBUG==1) {
                    printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("Database error. Contact your administrator and report the problem.<br/>");
            }
            die();
        }
        //echo("<pre>db_count($sql): "); var_dump($rows); echo('</pre>');
	return($rows);
}

//db_uquery nie zwraca wynik�w, wysy�a tylko komend� SQL do serwera bazy
function db_uquery($sql) {
    global $LM_DEBUG, $LM_READONLY,$LM_DBENGINE;
    if ($LM_READONLY==1) {
		echo("<b>Read only mode.</b><br>");
		return;
	}
	$my_link=db_connect();
	$i=0;
	$result=array();
	
        try {
            $stmt = $my_link->query($sql);
        } catch(PDOException $ex) {
            loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
            if ($LM_DEBUG==1) {
                    printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("Database error. Contact your administrator and report the problem.<br/>");
            }
            die();
        }
	
        error_reporting(E_ALL & ~E_NOTICE);
	return($stmt->rowCount());
}

//zwraca wiersz tabeli, kt�rego pole $field zawiera warto�� $id, je�li rekord nie istnieje zwracana jest pusta tablica
function asoc_row($tablica,$field,$id) {
	foreach($tablica as $row) {
		if ($row[$field]==$id) return $row;
	}
	return array();
}

/*****************************************************************************
Funkcje dost�pu do plik�w tekstowych
*****************************************************************************/

//czyta plik tekstowy z danymi
function db_read($nazwa_pliku) {
    $uchwyt = fopen($nazwa_pliku, "r");
    $tresc = fread($uchwyt, filesize($nazwa_pliku));
    fclose($uchwyt);
    //$tresc=str_replace("\\\"","\"",$tresc);
    $tresc=stripslashes($tresc);
    $data=explode(',',$tresc);
    return $data;
}

//zapisuje plik tekstowy z danymi
function db_write($nazwa_pliku,$data) {
include('../config/config.php');
  if ($LM_READONLY==0) {
    $uchwyt = fopen($nazwa_pliku, "w");
    if (count($data)>0) {
	$tresc=implode(',',$data);
    }
    fwrite($uchwyt, $tresc);
    fclose($uchwyt);
  }
}

//czyta plik tekstowy z danymi
function db_read2($nazwa_pliku) {
    $uchwyt = fopen($nazwa_pliku, "r");
    $tresc = fread($uchwyt, filesize($nazwa_pliku));
    fclose($uchwyt);
    //$tresc=str_replace("\\\"","\"",$tresc);
    $tresc=stripslashes($tresc);
    $data=explode('|',$tresc);
    return $data;
}

//zapisuje plik tekstowy z danymi
function db_write2($nazwa_pliku,$data) {
include('../config/config.php');
  if ($LM_READONLY==0) {
    $uchwyt = fopen($nazwa_pliku, "w");
    if (count($data)>0) {
		$tresc=implode('|',$data);
    }
    fwrite($uchwyt, $tresc);
    fclose($uchwyt);
  }
}

/*****************************************************************************
Funkcje pobieraj�ce okre�lone tabele z bazy
*****************************************************************************/

//zwraca tabel�
function admini($opcje='') { //DEPRECATED!! DO NOT USE
	//echo("<br>admini(\"$opcje\");<br>");
	//$sql="SELECT * FROM admin $opcje";
	//$result=db_asocquery($sql);
	//return($result);
}

//zwraca tabel�
function message($opcje='') {
	global $USERSTABLE;
	$sql="SELECT m.*, a1.login AS od, a2.login AS do FROM `message` AS m LEFT JOIN `$USERSTABLE` AS a1 ON m.msgfrom = a1.`userID` LEFT JOIN `$USERSTABLE` AS a2 ON m.msgto = a2.`userID` $opcje ORDER BY m.id DESC";
	$result=db_asocquery($sql);
	return($result);
}

//zwraca tabel�
function message_sent($opcje='') {
	global $USERSTABLE;
	$sql="SELECT m.*, a1.login AS od, a2.login AS do FROM `message_sent` AS m LEFT JOIN `$USERSTABLE` AS a1 ON m.msgfrom = a1.`userID` LEFT JOIN `$USERSTABLE` AS a2 ON m.msgto = a2.`userID` $opcje ORDER BY m.id DESC";
	$result=db_asocquery($sql);
	return($result);
}

/******************************************* INNE **********************************************/

//konwertuje string na liczbe
function str2num($z) {
    settype($z,'integer');
    return $z;
}

//tworzy lini� <link href= do nag��wka HTML - s�u�y do obs�ugi sk�rek CSS
function applycss($css) {
	printf('<link type="text/css" href="%s" rel="stylesheet">',getUrl().$css);
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}

/**
 * Function returns lmeve URL path
 * Used to prevent RPO/PRSSI type of exploit
 * @return type
 */
function getUrl(){
  $a=parse_url(sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],
    $_SERVER['REQUEST_URI']
  ));
  if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']!=80 && $_SERVER['SERVER_PORT']!=443) {
      $port=":${_SERVER['SERVER_PORT']}"; 
  } else {
      $port='';
  }
  $path=preg_split('/[\w]+\.php/',$a['path']);
  return $a['scheme'].'://'.$a['host'].$port.$path[0];
}

?>

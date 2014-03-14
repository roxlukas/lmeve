<?
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

include_once("log.php");
include_once('../config/config.php');

function printerr($text) {
	echo("<br><table class=\"error\"><tr><td>$text</td></tr></table>");
	echo('<input type="button" value="&lt; Back" onclick="history.back();">');
}

function secureGETnum($field) {
	$what=$_REQUEST[$field];
	if (!empty($what)&&!ctype_digit($what)) {
		if(($what{0}=='-')&&(ctype_digit(substr($what,1)))) return '-'.substr($what,1);
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
	global $LM_DEBUG,$LM_DBENGINE;
    include('../config/config.php');
    
    if ($LM_DBENGINE=="MYSQL") {
		$ret=mysql_pconnect($LM_dbhost, $LM_dbuser, $LM_dbpass);
		if (!$ret) {
			$blad=mysql_error();
			loguj('../var/error.txt',"No connection to the database. MySQL reply: $blad");
			
			if ($LM_DEBUG==1) {
				printerr("No connection to the database.<br />MySQL reply: $blad");
			} else {
				printerr("No connection to the database. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		$odp=@mysql_select_db($LM_dbname);
		if (!$odp) {
			$blad=mysql_error();
			loguj('../var/error.txt',"Database selection error. MySQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Database selection error.<br />MySQL reply: $blad");
			} else {
				printerr("Database selection error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}	    
		$odp=mysql_query("SET CHARACTER SET 'utf8'", $ret);
		if (!$odp) {
			$blad=mysql_error();
			loguj('../var/error.txt',"Character set select error. MySQL reply: $blad");
			
			if ($LM_DEBUG==1) {
				printerr("Character set select error.<br />MySQL reply: $blad");
			} else {
				printerr("Character set select error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}	      
		return($ret);
    } else if ($LM_DBENGINE=="PGSQL") {
		$ret=pg_pconnect("host=$LM_dbhost port=5432 dbname=$LM_dbname user=$LM_dbuser password=$LM_dbpass");
		if (!$ret) {
			$blad=pg_last_error();
			loguj('../var/error.txt',"No connection to the database. PostgreSQL reply: $blad");
			
			if ($LM_DEBUG==1) {
				printerr("No connection to the database.<br />PostgreSQL reply: $blad");
			} else {
				printerr("No connection to the database. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
			    
		$odp=pg_set_client_encoding ($ret, 'utf8');
		if ($odp!=0) {
			$blad=pg_last_error();
			loguj('../var/error.txt',"Character set select error. PostgreSQL reply: $blad");
			
			if ($LM_DEBUG==1) {
				printerr("Character set select error.<br />PostgreSQL reply: $blad");
			} else {
				printerr("Character set select error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}	      
		return($ret);
    }
}

//db_query zwraca dwuwymiarow� tablic� z rekordami
function db_query($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
	
	if ($LM_DBENGINE=="MYSQL") {
		$my_result = mysql_query ($sql);
		if (!$my_result) {
			$blad=mysql_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql MySQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />MySQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		
			while ($line = mysql_fetch_row($my_result)) {
				$j=0;
					foreach ($line as $col_value) {
						$result[$i][$j]=$col_value;
						$j++;
					}
				$i++;
			}
			mysql_free_result($my_result);
	
		mysql_close($my_link);
	} else if ($LM_DBENGINE=="PGSQL") {
		$sql=str_replace ('`','"',$sql);
		$my_result = pg_query($sql);
		if (!$my_result) {
			$blad=pg_last_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql PostgreSQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />PostgreSQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		
			while ($line = pg_fetch_row($my_result)) {
				$j=0;
					foreach ($line as $col_value) {
						$result[$i][$j]=$col_value;
						$j++;
					}
				$i++;
			}
			pg_free_result($my_result);
	
		pg_close($my_link);
	}
	return($result);
}

//db_asocquery zwraca asocjacyjn� tablic� z rekordami
function db_asocquery($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
	
	if ($LM_DBENGINE=="MYSQL") {
		$my_result = mysql_query ($sql);
		if (!$my_result) {
			$blad=mysql_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql MySQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />MySQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		echo(mysql_error($my_link));
		
			while ($row = mysql_fetch_array($my_result, MYSQL_ASSOC)) {
				$result[$i]=$row; 
				$i++;
			}
			mysql_free_result($my_result);
	
		mysql_close($my_link);
	} else if ($LM_DBENGINE=="PGSQL") {
		$sql=str_replace ('`','"',$sql);
		$my_result = pg_query ($sql);
		if (!$my_result) {
			$blad=pg_last_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql PostgreSQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />PostgreSQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		echo(pg_last_error($my_link));
			while ($row = pg_fetch_array($my_result)) {
				$result[$i]=$row; 
				$i++;
			}
			pg_free_result($my_result);
		pg_close($my_link);
	}
	return($result);
}

//db_count zwraca ilo�� rekord�w wybranych przez zapytanie
function db_count($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
	if ($LM_DBENGINE=="MYSQL") {
		$my_result = mysql_query ($sql);
		if (!$my_result) {
			$blad=mysql_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql MySQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />MySQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		$rows = mysql_num_rows($my_result);
		mysql_close($my_link);
	} else if ($LM_DBENGINE=="PGSQL") {
		$sql=str_replace ('`','"',$sql);
		$my_result = pg_query ($sql);
		if (!$my_result) {
			$blad=pg_last_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql PostgreSQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />PostgreSQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		$rows = pg_num_rows($my_result);
		pg_close($my_link);
	}
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
	if ($LM_DBENGINE=="MYSQL") {
		$my_result = mysql_unbuffered_query ($sql);
		if (!$my_result) {
			$blad=mysql_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql MySQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />MySQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		mysql_close($my_link);
	} else if ($LM_DBENGINE=="PGSQL") {
		$sql=str_replace ('`','"',$sql);
		$my_result = pg_query ($sql);
		if (!$my_result) {
			$blad=pg_last_error($my_link);
			loguj('../var/error.txt',"Error in query: $sql PostgreSQL reply: $blad");
			if ($LM_DEBUG==1) {
				printerr("Error in query: $sql<br />PostgreSQL reply: $blad");
			} else {
				printerr("Database error. Contact your administrator and report the problem.<br/>");
			}
			die('');
		}
		pg_close($my_link);
	}
	return($my_result);
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
	printf('<link type="text/css" href="%s" rel="stylesheet">',$css);
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}

?>

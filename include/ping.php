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

include_once("regexp.php");
function ping2a($adres) {
	if (!is_ip($adres)) die("Adres IP <b>$adres</b> nie jest poprawnym adresem IP<br>");
    $maxtries=3;	//ile prob jesli wypadnie pierwszy pakiet
    for ($i=0; $i<$maxtries; $i++) {
	$komenda=sprintf("ping -w 1 -c 1 %s|grep 'time='",$adres);
	$odpowiedz=exec($komenda);
	if ($odpowiedz=='') {
		$temp=999;
	} else {
		$odp=explode('=',$odpowiedz);
		$odp2=explode(' ',$odp[3]);
		$temp=$odp2[0];
	}
	if ($temp<999) break; //jesli pakiet nie wypadl - nie pinguj wiecej.
    }
    return $temp;
}

function ping2b($ping) {
	$lag=100;	// powyzej ilu ma sie pojawiac "obciazone"
	if ($ping==999) {
		echo('<img src="'.getUrl().'img/red.gif" alt="red"> 999 ms');
	} else {
		if ($ping<$lag) {
			echo('<img src="'.getUrl().'img/green.gif" alt="green"> ');
		} else {
			echo('<img src="'.getUrl().'img/yellow.gif" alt="yellow"> ');
		}
		echo($ping);
		echo(' ms');
	}
	return $temp;
}

function ping2bb($ping) {
	$lag=100;	// powyzej ilu ma sie pojawiac "obciazone"
	if ($ping==999) {
			echo('<img src="'.getUrl().'img/red.gif" alt="red">');
	} else {
		if ($ping<$lag) {
			echo('<img src="'.getUrl().'img/green.gif" alt="green"> ');
		} else {
			echo('<img src="'.getUrl().'img/yellow.gif" alt="yellow"> ');
		}
	}
	//return $temp;
}

function ping2c($adres) {
	if (!is_ip($adres)) die("Adres IP <b>$adres</b> nie jest poprawnym adresem IP<br>");
    $maxtries=1;	//ile prob jesli wypadnie pierwszy pakiet
    for ($i=0; $i<$maxtries; $i++) {
	$komenda=sprintf("ping -w 1 -c 1 %s|grep 'time='",$adres);
	$odpowiedz=exec($komenda);
	if ($odpowiedz=='') {
		$temp=999;
	} else {
		$odp=explode('=',$odpowiedz);
		$odp2=explode(' ',$odp[3]);
		$temp=$odp2[0];
	}
	if ($temp<999) break; //jesli pakiet nie wypadl - nie pinguj wiecej.
    }
    return $temp;
}

function ping($adres) {
	$rtt=ping2a($adres);
	ping2b($rtt);
}

?>

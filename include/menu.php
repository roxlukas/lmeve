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

$menu[0]['name']='Timesheet';
$menu[0]['php']='0.php';
$menu[1]['name']='Tasks';
$menu[1]['php']='1.php';
$menu[8]['name']='Statistics';
$menu[8]['php']='8.php';
$menu[2]['name']='Inventory';
$menu[2]['php']='2.php';
$menu[3]['name']='Market';
$menu[3]['php']='3.php';
$menu[4]['name']='Messages';
$menu[4]['php']='4.php';
$menu[5]['name']='Settings';
$menu[5]['php']='5.php';
$menu[6]['name']='Wallet';
$menu[6]['php']='6.php';
$menu[7]['name']='Users';
$menu[7]['php']='7.php';
$menu[9]['name']='Characters';
$menu[9]['php']='9.php';
$menu[10]['name']='Database';
$menu[10]['php']='a.php';
$menu[11]['name']='Wiki';
$menu[11]['php']='b.php';
$menu[12]['name']='Killboard';
$menu[12]['php']='k.php';
$menu[254]['name']='About';
$menu[254]['php']='about.php';

function showMenuTab($id, $opcja) {
	global $menu;
        global $MOBILE;
        
        if ($MOBILE) return mobile_showMenuTab($id, $opcja);
        
	if ($id==$opcja) {
		echo	'<td class="menua">
			<a href="?id=';
		echo	$id;
		echo	'">';
		echo	$menu[$id]['name'];
		echo	'<br></td>';
	} else {
		echo	'<td class="menu">
			<a href="?id=';
		echo	$id;
		echo	'">';
		echo	$menu[$id]['name'];
		echo	'</a><br></td>';
	}
}

function mobile_showMenuTab($id, $opcja) {
	global $menu;
	if ($id==$opcja) {
                echo("<option value=\"?id=$id\" selected>".$menu[$id]['name']."</option>");
	} else {
		echo("<option value=\"?id=$id\">".$menu[$id]['name']."</option>");
	}
}

function showTabContents($id) {
	global $menu;
	if (!empty($menu[$id]['php'])) include($menu[$id]['php']); else echo('<h2>No such tab.</h2>');
}

function menu($id) {
	showMenuTab(0,$id);
	showMenuTab(8,$id);
        showMenuTab(12,$id);
	showMenuTab(1,$id);
	showMenuTab(9,$id);
	showMenuTab(10,$id);
        showMenuTab(11,$id);
	showMenuTab(2,$id);
	showMenuTab(3,$id);
	showMenuTab(4,$id);
	showMenuTab(5,$id);
	showMenuTab(6,$id);
	showMenuTab(7,$id);
}
?>

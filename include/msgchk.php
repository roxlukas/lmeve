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

	if ($_SESSION['status']!=1) die("Wrong script call. <a href=\"index.php\">LMeve</a>");
		//include('message.php');
		
		$message=message("WHERE `msgto`={$_SESSION['granted']} AND `msgread`=0");
		include('lang.php');
	$g=0;
	foreach($message as $row) {
	  $g++;
	}
	if ($g==1) {
		printf(' <b><div class="newmsg">Notification: <a href="?id=4"><img src="'.getUrl().'img/msgnew.gif" alt="MSGnew">You have 1 unread message.</a></div>');
	}
	if ($g>1) {
		printf(' <b><div class="newmsg">Notification: <a href="?id=4"><img src="'.getUrl().'img/msgnew.gif" alt="MSGnew"> You have %d unread messages.</a></div>',$g);
	}

?>

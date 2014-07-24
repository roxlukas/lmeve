<?php
if ($_SESSION['status']!=1) die("Wrong script call. <a href=\"index.php\">LMeve</a>");
	    $f=$_GET['id2'];
	    switch ($f) {
			case 0:
			include("60.php");  //wallet sumaries
			break;
	    }
	    
	    switch ($f) {
			case 9:
			include("69.php");  //wallet sumaries - old
			break;
	    }
		
?>

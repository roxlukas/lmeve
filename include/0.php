<?php
if ($_SESSION['status']!=1) die("Wrong script call. <a href=\"index.php\">LMever</a>");
	    $f=$_GET['id2'];
	    switch ($f) {
	    case 0:
		include("00.php");  //lista userow
		break;
	    //case 1:
		//include("01.php");  //edycja usera
		//break;
	    //case 111:
		//include("01ping.php");  //ping usera
		//break;
	    //case 2:
		//include("02.php");  //zapis usera
		//break;
	    //case 3:
		//include("03.php");  //kasuj usera
		//break;
	    //case 4:
		//include("04.php");  //zglaszanie problemu
		//break;
	}
	?>

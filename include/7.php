<?php
checksession(); //check if we are called by a valid session
	    $f=$_GET['id2'];
	    switch ($f) {
	    case 0:
		include("70.php");  //lista users
		break;
	    case 1:
		include("71.php");  //list roles
		break;
		case 2:
		include("72.php");  //edit user
		break;
		case 3:
		include("73.php");  //save user
		break;
		case 4:
		include("74.php");  //edit role
		break;
		case 5:
		include("75.php");  //save role
		break;
		case 6:
		include("76.php");  //delete role
		break;
	    }
?>

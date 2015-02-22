<?php
checksession(); //check if we are called by a valid session
	    $id2=$_GET['id2'];
	    if ($id2=='') $id2=0;
	    switch ($id2) {
	    case 0:
		include("10.php");  //My Tasks List
		break;
	    case 1:
		include("11.php");  //Edit Task
		break;
		case 2:
		include("12.php");  //Save Task
		break;
		case 3:
		include("13.php");  //All Tasks List
		break;
		case 4:
		include("14.php");  //Delete Task
		break;
		case 5:
		include("15.php");  //Choose Type
		break;
                case 6:
		include("16.php");  //Clear orphaned tasks
		break;
                case 7:
		include("17.php");  //Clear Expired non-recurring tasks
		break;
	    }
?>
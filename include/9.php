<?php
checksession(); //check if we are called by a valid session
	    $id2=$_GET['id2'];
	    if ($id2=='') $id2=0;
	    switch ($id2) {
	    case 0:
		include("90.php");  //Character List
		break;
		case 1:
		include("91.php");  //Edit Character
		break;
		case 2:
		include("92.php");  //Save Character
		break;
		case 3:
		include("93.php");  //Delete Character
		break;
		
		case 4:
		include("94.php");  //Add API Key
		break;
		case 5:
		include("95.php");  //Save API Key
		break;
		
		case 6:
		include("96.php");  //Show Basic Info about character
		break;
            
                
	    }
?>
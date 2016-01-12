<?php
//begin
    checksession(); //check if we are called by a valid session
    global $MOBILE;
//routing
    $id=12;
    $id2=$_GET['id2'];
//default route
    if ($id2=='') $id2=0;


//controller
    switch ($id2) {
        case 0:
            include("k0.php");  //killboard
            break;
	case 1:
            include("k1.php");  //view single kill
            break;

    }
?>


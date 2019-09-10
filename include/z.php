<?php
checksession(); //check if we are called by a valid session
	    $id2=$_GET['id2'];
	    if ($id2=='') $id2=0;
            
            $title = generate_title("MG");
            $description = "MG";
            generate_meta($description, $title);
            
	    switch ($id2) {
                case 0:
		include("z0.php");  //MG01
		break;

            
                
	    }
?>
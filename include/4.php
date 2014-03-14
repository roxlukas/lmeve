<?php
//begin
    checksession(); //check if we are called by a valid session
//routing
    $id2=$_GET['id2'];
//default route
    if ($id2=='') $id2=0;
//submenu
    ?>
    <table border="0" cellspacing="2" cellpadding="0">
		    <tr><td>
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="1">
		    <input type="hidden" name="nr" value="new">
		    <input type="submit" value="New message">
		</form>
			</td><td>
                <form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="0">
		    <input type="submit" value="Inbox">
		</form>	
                         </td><td>   
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="5">
		    <input type="submit" value="Delete all">
		</form>			
			</td><td>
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="6">
		    <input type="submit" value="Sent">
		</form>
                        </td><td>
		<form method="get" action="">
		    <input type="hidden" name="id" value="4">
		    <input type="hidden" name="id2" value="8">
		    <input type="submit" value="Delete all sent">
		</form>			
    </td></tr></table>
    <?php
//end submenu

//controller
    switch ($id2) {
	    case 0:
		include("40.php");	//listuj wiadomosci
		break;
	    case 1:
		include("41.php");	//edytuj wiadomosc
		break;
	    case 2:
		include("42.php");	//zapisz wiadomosc
		break;
	    case 3:
		include("43.php");	//otworz wiadomosc
		break;
	    case 4:
		include("44.php");	//kasuj wiadomosc
		break;
		case 5:
		include("45.php");	//kasuj wszystkie
		break;
	    case 6:
		include("46.php");	//listuj wiadomosci wyslane
		break;		
		case 7:
		include("47.php");	//otworz wiadomosc wyslana
		break;
	    case 8:
		include("48.php");	//kasuj wszystkie wyslane
		break;
	}
?>

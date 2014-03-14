<?php
//begin
    checksession(); //check if we are called by a valid session
//routing
    $id2=$_GET['id2'];
//default route
    if ($id2=='') $id2=0;
//submenu
    ?>
    <table border="0" cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewMarket")) { ?>
    <td>
    <form method="get" action="">
    <input type="hidden" name="id" value="3">
    <input type="hidden" name="id2" value="0">
    <input type="submit" value="Market">
    </form>
    </td>
    <?php }
    ?>
    <?php if (checkrights("Administrator,ViewBuyCalc")) { ?>
    <td>
    <form method="get" action="">
    <input type="hidden" name="id" value="3">
    <input type="hidden" name="id2" value="1">
    <input type="submit" value="Buy Calculator">
    </form>
    </td>
    <?php }
    ?>
    </tr></table>
    <?php
//end submenu

//controller
    switch ($id2) {
	    case 0:
		include("30.php");  //Main Market Page
		break;
	    case 1:
		include("31.php");  //Buy Calc
		break;
            case 2:
		include("32.php");  //Buy Calc Quote
		break;
            case 3:
		include("33.php");  //View Buy Order
		break;
	    }
?>
<?php
//begin
    checksession(); //check if we are called by a valid session
//routing
    $id2=$_GET['id2'];
//default route
    if ($id2=='') $id2=0;
//submenu
    ?>
    <table cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewInventory")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="2" />
        <input type="hidden" name="id2" value="0" />
        <input type="submit" value="Inventory" />
        </form></td>
    <?php } ?>
    <?php if (checkrights("Administrator,ViewPOS")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="2" />
        <input type="hidden" name="id2" value="1" />
        <input type="submit" value="Control Towers" />
        </form></td>
    <?php } ?>
    <?php if (checkrights("Administrator,ViewPOS")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="2" />
        <input type="hidden" name="id2" value="2" />
        <input type="submit" value="Labs & Assembly Arrays" />
        </form></td>
    <?php } ?> 
    <?php if (checkrights("Administrator,ViewPOS")) { ?>
        <td><form action="" method="get">
        <input type="hidden" name="id" value="2" />
        <input type="hidden" name="id2" value="6" />
        <input type="submit" value="POCOs" />
        </form></td>
    <?php } ?> 
    </tr>
    </table>
    <?php
//end submenu

//controller
    switch ($id2) {
        case 0:
            include("20.php");  //Inventory
            break;
        case 1:
            include("21.php");  //Towers
            break;
        case 2:
            include("22.php");  //Labs
            break;
        case 3:
            include("23.php");  //Edit Labs
            break;
        case 4:
            include("24.php");  //Save Labs
            break;
        case 5:
            include("25.php");  //Delete Labs
            break;
        case 6:
            include("26.php");  //POCO list
            break;
    }
?>



<?php
//begin
    checksession(); //check if we are called by a valid session
//routing
    $id2=secureGETnum('id2');
    $wikipage=strip_tags(secureGETstr('wikipage',32));
//default route
    if ($id2=='') $id2=0;
    if ($wikipage=='') $wikipage='start';
//submenu
    
    ?>
    <table border="0" cellpadding="0" cellspacing="2">
    <tr>
    <?php if (checkrights("Administrator,ViewWiki")) { ?>
    <td>
    <form method="get" action="">
    <input type="hidden" name="id" value="11" />
    <input type="hidden" name="id2" value="0" />
    <input type="hidden" name="wikipage" value="start" />
    <input type="submit" value="Start" />
    </form>
    </td>
    <?php } ?>
    <?php if (checkrights("Administrator,EditWiki")) { ?>
    <td>
    <form method="get" action="">
    <input type="hidden" name="id" value="11" />
    <input type="hidden" name="id2" value="1" />
    <input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>" />
    <input type="submit" value="Edit page" />
    </form>
    </td>
    <?php } ?>
    <?php if (checkrights("Administrator,EditWiki")) { ?>
    <td>
    <form method="get" action="">
    <input type="hidden" name="id" value="11" />
    <input type="hidden" name="id2" value="3" />
    <input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>" />
    <input type="submit" value="Delete page" />
    </form>
    </td>
    <?php } ?>
    </tr></table>
    <?php
    
//end submenu

//controller
    switch ($id2) {
	    case 0:
		include("b0.php");  //Wiki page view
		break;
	    case 1:
		include("b1.php");  //Wiki page edit
		break;
	    case 2:
		include("b2.php");  //Wiki page save
		break;
            case 3:
		include("b3.php");  //Wiki page delete
		break;

	    }
?>
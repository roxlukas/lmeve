<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewProfitCalc")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=10; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Profit Chart'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB,$EC_PRICE_TO_USE_FOR_SELL;

include_once('materials.php'); //material related subroutines

$marketGroupID=secureGETnum('marketGroupID');

if (!empty($marketGroupID)) {
	$wheremarket="=$marketGroupID";
} else {
	$wheremarket="IS NULL";
}

//BEGIN Clientside sorting:
?>
  <script type="text/javascript" src="jquery-tablesorter/jquery.tablesorter.min.js"></script>
  <link rel="stylesheet" type="text/css" href="jquery-tablesorter/blue/style.css">
  <script type="text/javascript">
    function finished() {
        console.log("finished!!!");
        addTSCustomParsers();

        //enable sorting
        $("#items").tablesorter({ 
     headers: {
            0: {
                sorter: false
            },
            1: {
                sorter: 'text'
            },
            2: {
                sorter: 'isk'
            },
            3: {
                sorter: 'isk'
            },
            4: {
                sorter: 'numsep'
            },
            5: {
                sorter: 'isk'
            },
            6: {
                sorter: 'procent'
            },
            7: {
                sorter: 'numsep'
            }
        }
    }); 
    }
  </script>
<?php
//END Clientside sorting
?>
	    <div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>


    <div id="loader"><em>Loading...</em></div>
    
            <table id="items" class="lmframework tablesorter" style="min-width:700px; width: 90%;">
            <thead><tr><th>
                    <b>Icon</b>
            </th><th>
                    <b>Name</b>
            </th><th>
                    <b>Manufacturing cost</b>
            </th><th>
                    <b>Market price</b>
            </th><th>
                    <b>Market volume</b>
            </th><th>
                    <b>Unit Profit</b>
            </th><th>
                    <b>Profit [%]</b>
            </th><th>
                    <b>Market Profitability (B isk)</b>
            </th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
    
    <script type="text/javascript">
        ajax_append_table('ajax.php?act=GET_PROFIT_CALC','items','loader',finished);
    </script>
            
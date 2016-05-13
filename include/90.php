<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters,EditCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

global $USERSTABLE;

?>	    

<table cellpadding="0" cellspacing="2">
	    <tr>
               
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="9">
		<input type="hidden" name="id2" value="4">
		<input type="submit" value="Link characters using API KEY">
		</form></td>
           
	    <?php if (checkrights("Administrator,EditCharacters")) { ?>
	    <td><form action="" method="get">
		<input type="hidden" name="id" value="9">
		<input type="hidden" name="id2" value="1">
		<input type="hidden" name="nr" value="new">
		<input type="submit" value="Link characters manually">
		</form></td>
		<?php } ?>
		</tr></table>

<div class="tytul">
		<?php echo($PANELNAME); ?><br>
	    </div>

	    
	    
	<?php
	
	if (!checkrights("Administrator,ViewAllCharacters")) {
		$WHERE='WHERE lmu.userID='.$_SESSION[granted];
	} else {
		$WHERE='';
	}
	
	$chars=db_asocquery("SELECT lmu.login, lmu.`userID`, lmc.charID, acm.name, lmu.`act` FROM lmchars lmc JOIN $USERSTABLE lmu ON lmc.userID=lmu.userID LEFT JOIN apicorpmembers acm ON acm.characterID = lmc.charID $WHERE ORDER BY login, name;");
	//var_dump($chars);
	$rearrange=array();
	
	foreach ($chars as $c) {
                //echo('Login: '); var_dump($c['login']);
                if (!is_null($c['login'])) {
                    $rearrange[$c['login']]['login']='<a href="?id=7&id2=2&nr='.stripslashes($c['userID']).'">'.stripslashes($c['login']).'</a>';
                    $rearrange[$c['login']]['active']=$c['act'];
                    //echo('[x]');
                } else {
                    $rearrange[$c['login']]['login']='unclaimed';
                    //echo('[-]');
                }
                //echo('<br/>');
                //echo('Name: '); var_dump($c['name']);
                if (!is_null($c['name'])) {
                    $rearrange[$c['login']]['chars'][stripslashes($c['charID'])]['name']=stripslashes($c['name']);
                    //echo('[x]');
                } else {
                    $rearrange[$c['login']]['chars'][stripslashes($c['charID'])]['name']='UNKNOWN CHARACTER';
                    $rearrange[$c['login']]['chars'][stripslashes($c['charID'])]['unknown']=true;
                    //echo('[-]');
                }
                //echo('<br/>');
		$rearrange[$c['login']]['chars'][stripslashes($c['charID'])]['charID']=stripslashes($c['charID']);
	}
        
        //echo('<pre>');
        //print_r($rearrange);
        //echo('</pre>');
	
	function hrefedit($nr) {
		global $MENUITEM;
		echo("<a href=\"index.php?id=9&id2=1&nr=$nr\" title=\"Click to edit owner of this Character\">");
	}
	
	function althrefedit($nr) {
		global $MENUITEM;
		echo("<a href=\"index.php?id=9&id2=6&nr=$nr\" title=\"Click to open character information\">");
	}	
        
        function delhrefedit($nr) {
		global $MENUITEM;
		echo("<a href=\"index.php?id=9&id2=3&nr=$nr\" title=\"Click to disconnect character\">");
	}
			
			if (!sizeof($rearrange)>0) {
				echo('<h3>There are no characters registered!</h3>');
			} else {
			?>
			<table class="lmframework">
			<tr><th width="150">
				<b>Owner login</b>
			</th><th width="300" style="text-align: center;">
				<b>Characters</b>
			</th>
			</tr>
			<?php
				foreach($rearrange as $row) {
					echo('<tr><td class="tab"  style="text-align: center;">');
                                        if ($row['active']==0) echo('<img src="'.getUrl().'ccp_icons/38_16_169.png" alt="[x]" style="vertical-align: middle;" />');
					echo("<strong>${row['login']}</strong>");
					echo('</td><td class="tab">');
	
					echo('<table cellspacing="0" width="100%" cellpadding="0">');
					foreach($row['chars'] as $contrib) {
						echo('<tr><td class="tab" width="32" style="padding: 0px;">');
						althrefedit($contrib['charID']);
							echo("<img src=\"https://imageserver.eveonline.com/character/${contrib['charID']}_32.jpg\" title=\"${contrib['name']}\" />");
						echo('</a>');
						echo('</td><td class="tab" width="240" style="text-align: left;">');
						althrefedit($contrib['charID']);
							echo(stripslashes($contrib['name']));
						echo('</a>');	
						echo('</td>');
						if (checkrights("Administrator,EditCharacters")) {
							echo('<td class="tab" width="50" style="text-align: right;">');
							if ($contrib['unknown']) {
                                                            delhrefedit($contrib['charID']); echo('[Delete]'); echo('</a></td>');
                                                        } else {
                                                            hrefedit($contrib['charID']); echo('[Edit]'); echo('</a></td>');
                                                        }
						}
						echo('</tr>');
					}
					echo('</table>');
	
					echo('</td>');
					echo('</tr>');
				}
				echo('</table>');
			}
	
	?>


	

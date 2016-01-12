<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOwnCharacters,ViewAllCharacters")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=9; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Characters'; //Panel name (optional)
//standard header ends here

include_once("tasks.php");
include_once("killboard.php");

global $USERSTABLE,$LM_EVEDB;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}

?>		    <div class="tytul">
			<?php echo($PANELNAME); ?><br>
		    </div>
		    
<?php

		    $nr=$_GET['nr'];
		    if (!ctype_digit($nr)) {
				echo("Wrong parameter nr.");
				return;
		    }
			$nr=addslashes($nr);
			
			if (!checkrights("Administrator,ViewAllCharacters")) { 
				$sql="SELECT acm.`characterID` from `apicorpmembers` acm
				JOIN lmchars lmc
				ON lmc.charID=acm.characterID
				JOIN $USERSTABLE lmu
				ON lmu.userID=lmc.userID
				WHERE `characterID`=$nr AND lmu.userID=${_SESSION['granted']};";
			} else {
				$sql="SELECT `characterID` from `apicorpmembers` WHERE `characterID`=$nr";
			}
			
			if (db_count($sql)==0) {
				echo("Such record does not exist.");
				return;
			}
			//$year=date("Y");
			//$month=date("m");
			$char=db_asocquery("SELECT * from `apicorpmembers` WHERE `characterID`=$nr");
			$char=$char[0];
			$corp=db_asocquery("SELECT * from `apicorps` WHERE `corporationID`=${char['corporationID']}");
		    $corp=$corp[0];
		    $stats=db_asocquery("SELECT `activityName`, COUNT(*) AS jobs,SUM(TIME_TO_SEC(TIMEDIFF(`endProductionTime`,`beginProductionTime`))/3600) AS hours
			FROM `apiindustryjobs` aij
			JOIN $LM_EVEDB.`ramActivities` rac
			ON aij.activityID=rac.activityID
			WHERE beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
			AND aij.installerID=${char['characterID']}
			GROUP BY `activityName`
			ORDER BY `activityName`;");
			
			$sql="SELECT `typeID` , `typeName` , `name` , `installerID`, rac.activityName, SUM(runs)*portionSize AS runCount, COUNT(jobID) AS jobsCount
FROM apiindustryjobs aij
JOIN $LM_EVEDB.ramActivities rac ON aij.`activityID` = rac.`activityID`
JOIN $LM_EVEDB.invTypes inv ON aij.`outputTypeID` = inv.`typeID`
JOIN apicorpmembers acm ON aij.`installerID` = acm.`characterID`
WHERE rac.`activityID` IS NOT NULL
AND beginProductionTime BETWEEN '${year}-${month}-01' AND LAST_DAY('${year}-${month}-01')
AND `installerID` = $nr
GROUP BY `typeID` , `typeName` , `name` , `installerID`, rac.activityName
ORDER BY name ASC, typeName ASC, SUM( runs ) DESC;";
			$industry_tasks=db_asocquery($sql);
		    
		    echo('<table border="0" cellspacing="2" cellpadding=""><tr><td width="256" class="tab">');
		    echo("<img src=\"https://imageserver.eveonline.com/character/${char['characterID']}_256.jpg\" title=\"${char['name']}\" />");
		    echo('</td><td width="256" class="tab" style="vertical-align:top;">');
		    echo("<h2>${char['name']}</h2>");
		    if (!empty($char['title'])) echo("${char['title']}<br>");	
                    $corp['corporationName']=str_replace(' ','&nbsp;',$corp['corporationName']);
		    echo("<h3><img src=\"https://imageserver.eveonline.com/Corporation/${corp['corporationID']}_64.png\" style=\"vertical-align: middle;\"> ${corp['corporationName']}</h3>");
		    echo("<strong>Joined:</strong> ${char['startDateTime']}<br>");
		    if (!empty($char['base'])) echo("<strong>Base:</strong> ${char['base']}<br>");
                    //real name ident
                    if (checkrights("Administrator,ViewRealNames")) {
                        $sql="SELECT lmu.login FROM `lmchars` lmc 
                        JOIN `$USERSTABLE` lmu
                        ON lmc.`userID`=lmu.`userID`
                        WHERE lmc.`charID`=$nr;";
                        $realnames=db_asocquery($sql);
                        if (count($realnames)==1) {
                            $realname=$realnames[0];
                            echo("<strong>Character owner:</strong> ${realname['login']}<br>");
                        }
                    }
		    echo('</td><td width="256" class="tab" style="vertical-align:top;">');
		    
					$sumstat=0.0;
					$sumjobs=0;
					echo("<h2>Statistics</h2>");
					echo('<table cellspacing="2" cellpadding="0" width="100%">');
					echo('<tr><td class="tab-header">');
					echo('Activity');
					echo('</td><td class="tab-header">');		
					echo('Jobs');
					echo('</td><td class="tab-header">');		
					echo('Hours');
					echo('</td></tr>');
					foreach($stats as $stat) {
						echo('<tr><td class="tab">');
						echo($stat['activityName']);
						echo('</td><td class="tab" style="text-align: right;">');
						echo(number_format($stat['jobs'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
						$sumjobs+=$stat['jobs'];
						echo('</td><td class="tab" style="text-align: right;">');
						echo(number_format($stat['hours'], 0, $DECIMAL_SEP, $THOUSAND_SEP));
						$sumstat+=$stat['hours'];
						echo('</td></tr>');
					}
					echo('<tr><td class="tab">');
					echo('<strong>TOTAL:</strong>');
					echo('</td><td class="tab" style="text-align: right;">');
					echo('<strong>');
					echo(number_format($sumjobs, 0, $DECIMAL_SEP, $THOUSAND_SEP));
					echo('</strong>');
					echo('</td><td class="tab" style="text-align: right;"><strong>');		
					echo(number_format($sumstat, 0, $DECIMAL_SEP, $THOUSAND_SEP));
					echo('</strong></td></tr>');
					echo('</table>');
			echo('</td></tr><tr><td class="tab" colspan="3">');
			echo('<h2>Produced items:</h2>');
			foreach($industry_tasks as $items) {
				echo("<img src=\"".getTypeIDicon($items['typeID'])."\" title=\"${items['typeName']}\n${items['activityName']}\nJobs: ${items['jobsCount']}\nAmount: ${items['runCount']}\" />");
			}
		    echo('</td></tr><tr><td class="tab" colspan="3">');
			echo('<h2>Tasks assigned:</h2>');
			
			$SELECTEDCHAR="lmt.characterID=$nr";
			if (checkrights("Administrator,ViewAllTasks")) {
				$MYTASKS='TRUE';
			} else {
				if ($chars=getMyChars()) {
                                    $MYTASKS="lmt.`characterID` IN ($chars)";
                                } else {
                                    $MYTASKS='FALSE';
                                }
			}
		
			$ORDERBY="ORDER BY a.typeName, a.name, a.activityName";
                        $ORDERBY_jobs="ORDER BY acm.name, aij.endProductionTime";
		
			$tasklist=getTasks($MYTASKS, $SELECTEDCHAR, $ORDERBY, $year, $month);
			showTasks($tasklist);
                        
                        if (checkrights("Administrator,ViewCurrentJobs")) {
                            $jobslist=getCurrentJobs(str_replace('lmt.','acm.',$MYTASKS), str_replace('lmt.','acm.',$SELECTEDCHAR), $ORDERBY_jobs);
                            echo('<h2>Currently in progress:</h2>');
                            showCurrentJobs($jobslist);
                        }
                    echo('</td></tr><tr><td class="tab" colspan="3">');
			echo('<h2>Recent Kills & Losses:</h2>');
			
			showKills(getKills(0, 0, 0, 0, $nr, 0, 10));
                        ?> <center><input type="button" value="More kills..." onclick="location.href='?id=12&id2=0&characterID=<?=$nr?>';"/></center> <?php
			
		    echo('</td></tr></table>');
		    
 
		    echo('<div class="tleft"><table border="0"><tr>');
		    echo('<td width="60" valign="top"><input type="button" value="Go back" onclick="history.back();"><br></td>');
		    echo('</tr></table></div>');
		?>

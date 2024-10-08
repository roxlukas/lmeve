<?php
//Blueprint and material related functions
include_once '../bin/libpoller.php';

function getBlueprintByProduct($typeID) {
        $DEBUG=FALSE;
        if (empty($typeID)) return FALSE;
	global $LM_EVEDB;
        $sql="SELECT ybp.*,itp.`typeName`,COALESCE(dgm.`valueInt`,dgm.`valueFloat`,0) AS techLevel 
            FROM $LM_EVEDB.`yamlBlueprintProducts` ybp 
            JOIN $LM_EVEDB.`invTypes` itp
            ON ybp.`blueprintTypeID`=itp.`typeID`
            LEFT JOIN $LM_EVEDB.`dgmTypeAttributes` dgm
            ON ybp.`productTypeID`=dgm.`typeID` AND dgm.`attributeID`=422
            WHERE ybp.`productTypeID`=$typeID
            AND `activityID` != 11;";
	$blueprint=db_asocquery($sql);
        if ($DEBUG) echo("<pre>$sql</pre>");
	if (count($blueprint)>0) {
            if ($DEBUG) echo("Found blueprint(s) in yamlBlueprintProducts: " . print_r($blueprint,TRUE) . "<br/>");
            if ($blueprint[0]['techLevel']==3 && count($blueprint)>1) {
                if ($DEBUG) echo("This is Tech III and has multiple blueprints (relics)<br/>");
                if ($DEBUG) echo('<pre>');
                if ($DEBUG) var_dump($blueprint);
                if ($DEBUG) echo('</pre>');
                foreach($blueprint as $relic) {
                    if (strstr($relic['typeName'], getConfigItem('T3relicType','Wrecked')) !== FALSE) {
                        if ($DEBUG) echo("Returning single relic<br/>");
                        return $relic;
                    }
                }
                if ($DEBUG) echo("Didn't found relic for typeID=$typeID , sorry<br/>");
            } else {
                if ($DEBUG) echo("Returning single blueprint<br/>");
                return $blueprint[0];
            }
	} else { //blueprint not found... maybe given typeID is a blueprint itself??
            $blueprint=db_asocquery("SELECT ybp.*,itp.`typeName`,COALESCE(dgm.`valueInt`,dgm.`valueFloat`) AS techLevel 
            FROM $LM_EVEDB.`yamlBlueprintProducts` ybp 
            JOIN $LM_EVEDB.`invTypes` itp
            ON ybp.`blueprintTypeID`=itp.`typeID`
            JOIN $LM_EVEDB.`dgmTypeAttributes` dgm
            ON ybp.`productTypeID`=dgm.`typeID`
            WHERE ybp.`blueprintTypeID`=$typeID
            AND dgm.`attributeID`=422;");
            if (count($blueprint)>0) {
                if ($DEBUG) echo("Provided typeID is itself a blueprint<br/>");
                //ha! it's blueprint all right! told you!!
                return $blueprint[0];
            } else {
                if ($DEBUG) echo("Didn't found blueprint for typeID=$typeID , sorry<br/>");
                //not found either... mkay, return false
                return FALSE;
            }
	}
}

/**
 * Finds blueprint typeID for product typeID
 * 
 * @global type $LM_EVEDB - static data dump schema
 * @param $typeID - blueprint typeID
 */
function getBlueprintByProductOld($typeID) {
        if (empty($typeID)) return FALSE;
	global $LM_EVEDB;
	$blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invBlueprintTypes` WHERE `productTypeID` = $typeID;");
	//$techLevel=$blueprint[0][4];
	//$wasteFactor=$blueprint[0][11]/100;
	if (count($blueprint)==1) {
            return $blueprint[0];
	} else { //blueprint not found... maybe given typeID is a blueprint itself??
            $blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invBlueprintTypes` WHERE `blueprintTypeID` = $typeID;");
            if (count($blueprint)==1) {
                //ha! it's blueprint all right! told you!!
                return $blueprint[0];
            } else {
                //not found either... mkay, return false
                return FALSE;
            }
	}
}

/**
 * Finds typeID of Tech I BPO which produces a base item for Tech II BPO
 * 
 * @global type $LM_EVEDB - static data dump schema
 * @param type $typeID - Tech II BPO typeID
 * @return mixed typeID of Tech I BPO or False if not found
 */
function getT1BPOforT2BPO($typeID) {
        global $LM_EVEDB;
        $blueprint=db_asocquery("SELECT ybp.* 
                FROM $LM_EVEDB.`yamlBlueprintProducts` ybp
                JOIN $LM_EVEDB.`invTypes` itp
                ON ybp.`blueprintTypeID`=itp.`typeID`
                WHERE `productTypeID`=$typeID;");
        if (count($blueprint)>=1) { //it's exactly 1 for most items, but some T1 items have two possible Tech II outcomes, so >= 1
            return $blueprint[0];
	} else { //blueprint not found... maybe given typeID is a blueprint itself??
            return FALSE;
	}
}

function getRelicForT3BPC($typeID) {
        global $LM_EVEDB;
        $relicType=getConfigItem('T3relicType','Wrecked');
        $blueprint=db_asocquery("SELECT ybp.* 
                FROM $LM_EVEDB.`yamlBlueprintProducts` ybp
                JOIN $LM_EVEDB.`invTypes` itp
                ON ybp.`blueprintTypeID`=itp.`typeID`
                WHERE `productTypeID`=$typeID
                AND itp.`typeName` LIKE '%$relicType%';");
        if (count($blueprint)>=1) {
            return $blueprint[0];
	} else { //blueprint not found... maybe given typeID is a blueprint itself??
            return FALSE;
	}
}

/**
 * Returns the refine/reprocess portion size
 * 
 * @global type $LM_EVEDB - static data dump schema
 * @param type $typeID - typeID of the item in question
 * @return mixed portion size or false if not found 
 */
function getPortionSize($typeID) {
    global $LM_EVEDB;
    $portionSize=db_asocquery("SELECT `portionSize` FROM $LM_EVEDB.`invTypes` WHERE `typeID`=$typeID");
    if (count($portionSize)==1) {
	return $portionSize[0]['portionSize'];
    } else {
        return FALSE;
    }
}

function getTechLevel($typeID) {
	if ($bpo=getBlueprintByProduct($typeID)) return $bpo['techLevel']; else return FALSE;
}

function getWasteFactor($typeID) {
	if ($bpo=getBlueprintByProduct($typeID)) return $bpo['wasteFactor']/100; else return FALSE;
}

function getMEPE($typeID) {
	$settings=db_asocquery("SELECT * FROM `cfgbpo` WHERE `typeID` = $typeID;");
	if (count($settings)==1) {
		return $settings[0];
	} else {
		return FALSE;
	}
}

function getBlueprint($typeID) {
	global $LM_EVEDB;
	$blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invBlueprintTypes` WHERE `blueprintTypeID` = $typeID;");
	//$techLevel=$blueprint[0][4];
	//$wasteFactor=$blueprint[0][11]/100;
	if (count($blueprint)==1) {
		return $blueprint[0];
	} else {
		return FALSE;
	}
}

/**
 * get base materials for typeID (with perfect ME)
 * 
 * @global type $LM_EVEDB
 * @param type $typeID
 * @return mixed array with materials or false if there are none
 */
function getRecycleMaterials($typeID) {
	global $LM_EVEDB;
	$sql="SELECT inv.typeName, mat.quantity, inv.typeID
		FROM $LM_EVEDB.`invTypeMaterials` AS mat
		JOIN $LM_EVEDB.`invTypes` AS inv
		ON mat.materialTypeID = inv.typeID
		WHERE mat.typeID=$typeID";
	$recycle=db_asocquery($sql);
	if (count($recycle)>0) {
		return $recycle;
	} else {
		return FALSE;
	}	
}

function getRecycleMaterialsOres($typeID = null) {
    global $LM_EVEDB;
  
    if (is_null($typeID)) {
            $wheretypeid = 'TRUE'; 
    } else {
        $wheretypeid = "itp.`typeID`=$typeID";
    }
    
    $sql="SELECT itp.`typeID`, itp.`typeName`, itmtp.`typeName` AS `mineralTypeName`, itp.`volume`, itp.`portionSize`, itm.`materialTypeID` AS mineralID, itm.`quantity`, apr.`max` AS price
    FROM $LM_EVEDB.`invTypes` itp
    JOIN $LM_EVEDB.`invGroups` igp ON itp.`groupID` = igp.`groupID` 
    JOIN $LM_EVEDB.`invTypeMaterials` itm ON itp.`typeID` = itm.`typeID`
    JOIN $LM_EVEDB.`invTypes` itmtp ON itm.`materialTypeID` = itmtp.`typeID`
    LEFT JOIN `apiprices` apr ON itm.`materialTypeID` = apr.`typeID`
    WHERE igp.`categoryID` = 25
    AND itp.`typeName` NOT LIKE 'Compressed%'
    AND itp.`marketGroupID` IS NOT NULL
    AND itp.`volume` < 50
    AND (apr.`type`='buy' OR apr.`type` IS NULL)
    AND $wheretypeid
    ORDER BY itp.`groupID`,itp.`typeID`";
    
    $recycle=db_asocquery($sql);
    if (count($recycle)>0) {
            return $recycle;
    } else {
            return FALSE;
    }
}

/**
 * Pre Crius function - deprecated
 * 
 * @deprecated
 */
//getBaseMaterials($typeID) - get recyclable (base) materials for typeID
//$typeID - ITEM typeID
function getBaseMaterialsOld($typeID,$runs=1,$melvl_override=null) {
	global $LM_EVEDB;
	$recycle=getRecycleMaterials($typeID);
	$bpo=getBlueprintByProduct($typeID);
        
        //deprecated! no extra mats anymore
	//$materials=getExtraMats($typeID,1); //get extra mats for manufacturing (activityID = 1)

	
	$techLevel=$bpo['techLevel'];
	
	if ($recycle!=false) {
			//echo("DEBUG: count(\$recycle)>0<br>");
			if ($techLevel==2) { //Subtract T1 materials from T2 recycle for Tech II
				//echo("DEBUG: Tech Level==2<br>");

				$tech1itemID=0;
                                if ($materials) {
                                    foreach ($materials as $row) {
                                            //echo("DEBUG: Looking for recycle material, typeID={$row['typeID']}, recycle={$row['recycle']}<br>");
                                            if ($row['recycle']==1) $tech1itemID=$row['typeID']; //if recycle=1, then found it
                                    }
                                }
				if ($tech1itemID!=0) { //if found Tech 1 item, query its materials
					//echo("DEBUG: TECH 1 FOUND, typeID=$tech1itemID<br>");
					$rawt1recycle=getRecycleMaterials($tech1itemID);
					//echo('DEBUG: $rawt1recycle=');var_dump($rawt1recycle);echo('<br>');
					foreach($rawt1recycle as $row) {
						$t1recycle[$row['typeID']]=$row['quantity'];
					}
					//echo('DEBUG: $t1recycle=');var_dump($t1recycle);echo('<br>');
				}
			}
			foreach ($recycle as $k => $row) {
				if ($t1recycle[$row['typeID']]>0) {
					$recycle[$k]['quantity']=($row['quantity']-$t1recycle[$row['typeID']]);
				} else {
                                        $recycle[$k]['quantity']=$row['quantity'];
                                }
				if ($recycle[$k]['quantity']<=0) unset($recycle[$k]);
			}
			//echo('DEBUG: $recycle=');var_dump($recycle);echo('<br>');
                        //ME modification here!
                        if ($set=getMEPE($typeID)) {
                                $melevel=$set['me'];
                                $pelevel=$set['pe'];
                        }
                        switch ($techLevel) {
                                case 2:
                                        if (!isset($melevel)) $melevel=0;
                                        if (!isset($pelevel)) $pelevel=0;
                                        break;
                                case 3:
                                        if (!isset($melevel)) $melevel=0;
                                        if (!isset($pelevel)) $pelevel=0;
                                        break;
                                default:
                                        if (!isset($melevel)) $melevel=0;
                                        if (!isset($pelevel)) $pelevel=0;
                        }
                        if (!is_null($melvl_override)) {
                            $melevel=$melvl_override;
                        }
                        //old formulas (pre-Crius)
                        $wasteFactor=getWasteFactor($typeID);
                        if ($melevel>=0) {
                            $multiplier=1+($wasteFactor/(1 + $melevel));
                            $waste=$wasteFactor/(1 + $melevel)*100;
                        } else {
                            $multiplier=1+($wasteFactor*(1 - $melevel));
                            $waste=$wasteFactor*(1 - $melevel)*100;
                        }
                        //new formulas (post-Crius)
                        if ($melevel>10) $melevel=10;
                        $multiplier=1-(0.01*$melevel);
                        $waste=$melevel;
                        
                        foreach($recycle as $i => $row) {
                            $recycle[$i]['quantity']=$runs*$row['quantity'];
                            $recycle[$i]['notperfect']=$runs*round($row['quantity']*$multiplier);
                            $recycle[$i]['waste']=$waste;
                        }
                        //end ME modification
			return $recycle;
		}
         return false;
}

function getBaseMaterials($typeID,$runs=1,$melvl_override=null,$activityID=1) {
	global $LM_EVEDB;
	$DEBUG_MODE=FALSE;
        
	$bpo=getBlueprintByProduct($typeID);
        
        $typeID=$bpo['blueprintTypeID'];
        
        if (empty($typeID)) {
            echo("Error: getBlueprintByProduct() returned empty typeID");
            return FALSE;
        }
        
	$techLevel=$bpo['techLevel'];
        
        $sql="SELECT ybm.`materialTypeID` AS `typeID`, itp.`typeName`, ybm.`quantity`, 0 AS `damagePerJob`, 0 AS `recycle`
            FROM `$LM_EVEDB`.`yamlBlueprintMaterials` ybm
            JOIN `$LM_EVEDB`.`invTypes` itp
            ON ybm.`materialTypeID` = itp.`typeID`
            WHERE ybm.`blueprintTypeID` = $typeID
            AND `activityID` = $activityID
            ORDER BY ybm.`materialTypeID`";
        
        $materials=db_asocquery($sql);
        
        if ($DEBUG_MODE) {
            echo("<h2>DEBUG getBaseMaterials()</h2>");
            echo("<pre>$sql</pre>");
            echo("<pre>".print_r($materials,true)."</pre>");
            echo("<h2>END DEBUG</h2>");
        }
	
	if ($set=getMEPE($typeID)) {
                $melevel=$set['me'];
                $pelevel=$set['pe'];
        }
        
        switch ($techLevel) {
                case 2:
                        if (!isset($melevel)) $melevel=0;
                        if (!isset($pelevel)) $pelevel=0;
                        break;
                case 3:
                        if (!isset($melevel)) $melevel=0;
                        if (!isset($pelevel)) $pelevel=0;
                        break;
                default:
                        if (!isset($melevel)) $melevel=0;
                        if (!isset($pelevel)) $pelevel=0;
        }
        
       
        if (!is_null($melvl_override)) {
            $melevel=$melvl_override;
        }

        //new formulas (post-Crius)
        if ($melevel>10) $melevel=10;
        $multiplier=1-(0.01*$melevel);
        $waste=$melevel;

        foreach($materials as $i => $row) {
            $materials[$i]['quantity']=$runs*$row['quantity'];
            $materials[$i]['notperfect']=$runs*ceil($row['quantity']*$multiplier);
            $materials[$i]['waste']=$waste;
        }
        
        //inject relics for tech III jobs :D
        if ($techLevel==3 && $activityID==8) {
            if ($DEBUG_MODE) echo("This is Tech III job<br/>");
            $relic=db_asocquery("SELECT `typeID`,`typeName`,$runs AS `quantity`, 0 AS `damagePerJob`, 0 AS `recycle`, $runs AS `notperfect`, 0 AS `waste`
                    FROM $LM_EVEDB.`invTypes`
                    WHERE typeID=$typeID;");
            if(count($relic)!=0) {
                if ($DEBUG_MODE) echo("Injecting relic as material<br/>");
                array_push($materials, $relic[0]);
            }
        }
        
        //end ME modification
        return $materials;

}

function displayBaseMaterials($recycle,$melevel=0,$wasteFactor=0) {
        //$melevel, $wasteFactor - deprecated!
	if ($recycle!=false) {
                //getBaseMaterials() takes care of waste and me level now
		/*if ($melevel>=0) {
			$multiplier=1+($wasteFactor/(1 + $melevel));
			$waste=$wasteFactor/(1 + $melevel)*100;
		} else {
			$multiplier=1+($wasteFactor*(1 - $melevel));
			$waste=$wasteFactor*(1 - $melevel)*100;
		}*/
                $waste=$recycle[0]['waste'];
		printf("<strong>Material reduction based on ME level:</strong> %4.2f%%",$waste);
		
		echo("<table class=\"lmframework\" width=\"100%\">");
		echo("<tr colspan=\"2\"><th>Material</th><th>Quantity</th></tr>");
		//draw Material list
		foreach ($recycle as $row) {
			//$notperfect=round($row['quantity']*$multiplier);
                        $notperfect=$row['notperfect'];
			echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr={$row['typeID']}\"><img src=\"".getTypeIDicon($row['typeID'])."\" style=\"width: 16px; height: 16px; float: left;\" /> {$row['typeName']}</a></td><td><strong>{$notperfect}</strong> (base: {$row['quantity']})</td></tr>");
		}
		echo("</table>");
	}
	return;
}

//getSkills($typeID,$activityID) - get skills for typeID and activityID
//$typeID - ITEM typeID
//$activityID - ID of activity: 1-Manufacturing 5-Copying 8-Invention, etc.
function getSkills($typeID,$activityID) {
    $DEBUG=FALSE;
    if (empty($typeID)) {
        if ($DEBUG) echo("getSkills called with null typeID, exiting<br/>");
        return FALSE;
    }
    $bpo=getBlueprintByProduct($typeID);
    $bpoID=$bpo['blueprintTypeID'];
    if (empty($bpoID)) {
        if ($DEBUG) echo("getSkills got null blueprintID from getBlueprintByProduct<br/>");
        return FALSE;
    }
    if ($DEBUG) echo("getSkills got blueprintID=$bpoID<br/>");
	global $LM_EVEDB;
	$sql="SELECT ybs.`skillTypeID`, ybs.`level`, itp.`typeName`
        FROM `$LM_EVEDB`.`yamlBlueprintSkills` ybs
        JOIN `$LM_EVEDB`.`invTypes` itp
        ON ybs.`skillTypeID`=itp.`typeID`
	WHERE `blueprintTypeID`=$bpoID
        AND `activityID` = $activityID";
	$skills=db_asocquery($sql); //Skills
	if (count($skills)>0) {
            if ($DEBUG) echo("Returning skills for typeID=$typeID<br/>");
            return $skills;
	} else {
            if ($DEBUG) echo("Didn't find any skills for typeID=$typeID<br/>");
            return FALSE;
	}
}

function displaySkills($skills) {
	if ($skills!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"2\"><th>Skill</th><th>Required level</th></tr>");
			foreach ($skills as $row) {
				if ($row['level']>0)	echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr={$row['skillTypeID']}\"><img src=\"".getTypeIDicon($row['skillTypeID'])."\" style=\"width: 16px; height: 16px; float: left;\" /> {$row['typeName']}</a></td><td>{$row['level']}</td></tr>");
			}
			echo("</table>");
		}
	return;
}

/**
 * Get extra materials for typeID and activityID - deprecated
 * 
 * @global type $LM_EVEDB
 * @param type $typeID - typeID of item in question
 * @param type $activityID - ID of activity: 1-Manufacturing 5-Copying 8-Invention, etc.
 * @param type $runs - how many production runs? (default = 1)
 * @return mixed array with materials or false if there are none
 * @deprecated
 */
function getExtraMats($typeID,$activityID,$runs=1) {
        //echo("getExtraMats() DEBUG: typeID='$typeID', activityID='$activityID', runs='$runs' <br/>");
	global $LM_EVEDB;
        if ($activityID!=8) {
            //find original blueprint for everything but invention
            $bpo=getBlueprintByProduct($typeID);
        } else {
            //but for invention we have to find tech 1 blueprint
            $bpo=getT1BPOforT2BPO($typeID);
        }
	//echo('DEBUG: $bpo='); var_dump($bpo); echo('<br>');
	$BPtypeID=$bpo['blueprintTypeID'];
        if (empty($BPtypeID)) return false;
	$sql="SELECT itp.typeName, $runs * mat.quantity AS quantity, mat.damagePerJob, itp.typeID, mat.recycle
		FROM $LM_EVEDB.`ramTypeRequirements` AS mat
		JOIN $LM_EVEDB.`invTypes` AS itp
		ON mat.requiredTypeID = itp.typeID
		JOIN $LM_EVEDB.`invGroups` AS igr
		ON itp.groupID=igr.groupID
		WHERE mat.typeID=$BPtypeID
		AND igr.categoryID != 16
		AND mat.activityID = $activityID";
	$materials=db_asocquery($sql);
	if (count($materials)>0) {
		return $materials;
	} else {
		return FALSE;
	}
	//Extra Materials [0]=typeName [1]=quantity [2]=damagePerJob [3]=typeID [4]=recycle
}

/**
 * Deprecated
 * 
 * @param type $materials
 * @return type 
 * @deprecated
 */
function displayExtraMats($materials) {
	if ($materials!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"3\"><th>Extra Material</th><th>Quantity</th><th>dmg per job</th></tr>");
			foreach ($materials as $row) {
				$row['damagePerJob']=sprintf("%d%%",$row['damagePerJob']*100);
				if ($row['quantity']>0) echo("<tr colspan=\"3\"><td><a href=\"?id=10&id2=1&nr={$row['typeID']}\"><img src=\"".getTypeIDicon($row['typeID'])."\" style=\"width: 16px; height: 16px; float: left;\" /> {$row['typeName']}</a></td><td>{$row['quantity']}</td><td>{$row['damagePerJob']}</td></tr>");
			}
			echo("</table>");
		}
	return;
}

/**
 * Draw a HTML table with kit data (kit is a complete set of ingredients for a given amount of industry jobs)
 * 
 * @param array $recycle - base materials
 * @param array $materials - extra materials - not used post Crius
 * @param int $melevel - ME level - not used post Crius (calculated in GetBaseMaterials)
 * @param double $wasteFactor - waste factor - not used post Crius
 */
function displayKit2($recycle,$materials=null,$melevel=null,$wasteFactor=null,$location=false) { //NEW!
    if ($location) {
        echo("<table class=\"lmframework\" width=\"100%\">");
        echo("<tr><th colspan=\"2\" style=\"width: 100%\">Location</th></tr>");
        echo("<tr><td style=\"padding: 0px; width: 32px;\"><img src=\"".getTypeIDicon($location['typeID'])."\" title=\"{$location['typeName']}\"></td><td style=\"width: 95%;\"><strong>{$location['itemName']}</strong><br/>{$location['moonName']}</td></tr>");
	echo("</table>");		
    }
    if ($materials!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"2\"><th style=\"width: 67%\">Extra Materials</th><th>Quantity</th></tr>");
			foreach ($materials as $row) {
                            //data interface workaround
                            if (strpos($row['typeName'],'Data Interface')!==false) $row['quantity']=1;
			    if ($row['quantity']>0) echo("<tr colspan=\"3\"><td><a href=\"?id=10&id2=1&nr={$row['typeID']}\"><img src=\"".getTypeIDicon($row['typeID'])."\" style=\"width: 16px; height: 16px; float: left;\" /> {$row['typeName']}</a></td><td>".$row['quantity']*$row['damagePerJob']."</td></tr>");
			}
			echo("</table>");
    }
    if ($recycle!=false) {
		echo("<table class=\"lmframework\" width=\"100%\">");
		echo("<tr colspan=\"2\"><th style=\"width: 67%\">Materials</th><th>Quantity</th></tr>");
		//draw Material list
		foreach ($recycle as $row) {
			//$notperfect=round($row['quantity']*$multiplier);
                        if (strpos($row['typeName'],'Data Interface')!==false) $row['notperfect']=1;
                        $notperfect=$row['notperfect'];
			echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr={$row['typeID']}\"><img src=\"".getTypeIDicon($row['typeID'])."\" style=\"width: 16px; height: 16px; float: left;\" /> {$row['typeName']}</a></td><td>{$notperfect}</td></tr>");
		}
		echo("</table>");
    }
}

function getMaterialsForTasks($tasks) {
    $materials=array();
    if (count($tasks) > 0) {
        foreach ($tasks as $task) {
                $typeID=$task['typeID'];
                $activityID=$task['activityID'];
                $structureID=$task['structureID'];
                $runs=$task['runs'];

                if ($portionSize=getPortionSize($typeID)) {
                    $runs=$runs/$portionSize;
                }
                if (!isset($activityID)) $activityID=1;
                
                if ($activityID==8) { //invention materials are now bound to T1 BP, not T2 BP
                    $tmpBPO=getT1BPOforT2BPO($typeID);
                    //echo("<h2>Invention DEBUG</h2><pre>".print_r($tmpBPO,TRUE)."</pre>");
                    $typeID=$tmpBPO['blueprintTypeID'];
                }

                $tempmats=getBaseMaterials($typeID,$runs,null,$activityID);
                foreach ($tempmats as $tempmat) {
                    $requiredTypeID=$tempmat['typeID'];
                    $materials[$requiredTypeID]['typeID']=$requiredTypeID;
                    $materials[$requiredTypeID]['typeName']=$tempmat['typeName'];
                    $materials[$requiredTypeID]['quantity']+=$tempmat['quantity'];
                    $materials[$requiredTypeID]['notperfect']+=$tempmat['notperfect'];
                    $materials[$requiredTypeID]['waste']=$tempmat['waste'];
                }
         }
    }
    return $materials;
}

function displayFacilityKit($tasks) {
    
    if (count($tasks)>0) {
        $structureID = $tasks[0]['structureID'];
        $materials = getMaterialsForTasks($tasks);
         echo('<div style="width:400px;">');
         //$materials=array_msort($materials, array('typeName'=>SORT_ASC));
         displayKit2($materials,null,null,null,getLabDetails($structureID));
         echo('</div>');
     } else {
         echo('<h3>No materials required for this Facility</h3>');
     }
    
}

/**
 * Fetch EVE-central.com prices of given typeID
 * 
 * @param type $typeID - typeID in question
 * @return array or boolean
 */
function getEveCentralPrices($typeID) {
    $priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=$typeID;");
    if (count($priceData) > 0) return $priceData; else return false;
}

/**
 * Fetch EVE-central.com price of given $typeID, $type=[sell | buy ], minmax=[min | max | avg | median]
 * 
 * @param type $typeID - typeID in question
 * @return float or boolean
 */
function getEveCentralPrice($typeID,$type='sell',$minmax='min') {
    $priceData=db_asocquery("SELECT * FROM `apiprices` WHERE `typeID`=$typeID;");
    if (count($priceData) > 0) {
        foreach ($priceData as $row) {
            if ($row['type']==$type) {
                switch ($minmax) {
                    case 'min':
                        //echo("min: {$row['min']} <br/>");
                        return $row['min'];
                        break;
                    case 'max':
                        //echo("max: {$row['max']} <br/>");
                        return $row['max'];
                        break;
                    case 'avg':
                        //echo("avg: {$row['avg']} <br/>");
                        return $row['avg'];
                        break;
                    case 'median':
                        //echo("median: {$row['median']} <br/>");
                        return $row['median'];
                        break;
                    default:
                        return $row['avg'];
                }
            }
        }
    }
    return false;
}

/**
 * Calculates manufacturing cost for a given typeID
 * 
 * @param type $typeID - typeID of the item in question
 * @return array $return['quote'] (float) - calculated quote; $return['accurate'] (boolean) - is the price accurate?
 */
function calcManufacturingCost($typeID) {
    global $LM_EVEDB,$EC_PRICE_TO_USE_FOR_MAN;
    $DEBUG = FALSE;
    
    $returns=array();
    $returns['price']=0;
    $returns['accurate']=true;

    $techLevel=getTechLevel($typeID);
    
    if($DEBUG) echo("calcManufacturingCost($typeID) BEGIN<br/>");
    
    if($DEBUG) echo("\$techLevel = $techLevel<br/>");
    
    //ME and PE settings
    if ($mepe=getMEPE($typeID)) {
        $melevel=$mepe['me'];
        $pelevel=$mepe['pe'];
    }
    
    switch ($techLevel) {
        case 2:
            if (!isset($melevel)) $melevel=0;
            break;
	case 3:
            if (!isset($melevel)) $melevel=0;
            break;
	default:
            if (!isset($melevel)) $melevel=0;
    }
    
    if (!isset($pelevel)) $pelevel=0;
    
    if($DEBUG) echo("ME=$melevel TE=$pelevel<br/>");
 
    $baseMats=getBaseMaterials($typeID,1,$melevel);
    $portionSize=db_query("SELECT `portionSize` FROM `$LM_EVEDB`.`invTypes` WHERE `typeID`=$typeID");
    $portionSize=$portionSize[0][0];
    if (!isset($portionSize)) $portionSize=1;
   
    if ($DEBUG) echo("calcManufacturingCost($typeID) \$baseMats=" . print_r($baseMats, TRUE) . "<br>");
    
    //form a complete material list
    $completeMats = array();
    if($baseMats) {
        foreach ($baseMats as $mat) {
            //echo("{$mat['typeName']} = {$mat['quantity']} * $multiplier = ".$mat['quantity']*$multiplier."<br/>");
            $completeMats[$mat['typeID']]['qty']+=$mat['notperfect'];
            $completeMats[$mat['typeID']]['typeName']=$mat['typeName'];
        }
    }
    
    //now that we have a complete list of materials, we can try to calculate the price
    //echo("count=".count($completeMats)."<br/>");
    if ($DEBUG) echo("calcManufacturingCost($typeID) \$completeMats=" . print_r($completeMats, TRUE) . "<br>");
    
    if (count($completeMats)>0) {
        foreach ($completeMats as $id => $mat) {
            if (getBlueprintByProduct($id)) {
                if ($DEBUG) echo("calcManufacturingCost($typeID) getBlueprintByProduct($id)=". print_r(getBlueprintByProduct($id),TRUE)."<br>");
                $subcost=calcManufacturingCost($id);
                $returns['price']+=$mat['qty']*$subcost['price'];
                $returns['accurate']=$subcost['accurate']&&$returns['accurate'];
            } else {
                if ($unitPrice=getEveCentralPrice($id,$EC_PRICE_TO_USE_FOR_MAN['type'],$EC_PRICE_TO_USE_FOR_MAN['price'])) {  
                    if ($DEBUG) echo("$id - $unitPrice<br/>");
                    $returns['price']+=$mat['qty']*$unitPrice;
                } else {
                    $returns['accurate']=false;
                    if ($DEBUG) echo("Missing price: {$mat['typeName']}<br/>");
                }
            }
        }
        $returns['price']=$returns['price']/$portionSize;
        $returns['portionSize']=$portionSize;
        if ($DEBUG) echo("calcManufacturingCost($typeID) RETURN \$returns=" . print_r($returns, TRUE) . "<br>");
        return $returns;
    } else {
        return false;
    }
}

/**
 * Get list of available decryptors
 * 
 * @return array - list of decryptors and their parameters
 */
function getDecryptors() {
    global $LM_EVEDB;
    $decryptors = db_asocquery("SELECT *
        FROM `ramdecryptors` rd
        JOIN `$LM_EVEDB`.`invTypes` itp ON rd.`typeID` = itp.`typeID`");
    $ret = array();
    foreach ($decryptors as $d) {
        $ret[$d['typeID']] = $d;
    }
    return $ret;
}

/**
 * Get configured decryptor for a given typeID invention
 * 
 * @param type $typeID - typeID of the item (product) in question
 * @return array - decryptor parameters
 */
function getDecryptor($typeID) {
    global $LM_EVEDB;
    $selected = db_asocquery("SELECT *
        FROM `cfgdecryptors` cd
        JOIN `$LM_EVEDB`.`invTypes` itp ON cd.`decryptorTypeID` = itp.`typeID`
        JOIN `ramdecryptors` rd ON cd.`decryptorTypeID` = rd.`typeID`    
        WHERE cd.`typeID`=$typeID");
        
        if (is_array($selected) && count($selected > 0)) {
            $selected = $selected[0];
        } else {
            $selected = array();
            $selected['decryptorTypeID'] = 0;
        }
    return $selected;
}

/**
 * Calculates invention cost for a given typeID
 * 
 * @param type $typeID - typeID of the item (product) in question
 * @return array $return['quote'] (float) - calculated quote; $return['accurate'] (boolean) - is the price accurate?
 */
function calcInventionCost($typeID) {
    global $LM_EVEDB,$EC_PRICE_TO_USE_FOR_MAN;
    $DEBUG=FALSE;
    $returns=array();
    $returns['price']=0;
    $returns['accurate']=true;
    if ($t2bpo=getBlueprintByProduct($typeID)){
        $bptypeID=$t2bpo['blueprintTypeID']; //switch from item ID to Tech 2 BPO ID
    } else {
        return false;
    }
    //now check if we are dealing with Tech II or Tech III at all
    if ($t2bpo['techLevel']<2) {
        if ($DEBUG) echo("Invention requires the item to be at least Tech II or higher (this job is tech ".$t2bpo['techLevel'].")<br/>");
        return false;
    }
    if ($DEBUG) echo("Getting decryptor<br/>");
    $decryptor = getDecryptor($typeID);
    if ($DEBUG) print_r($decryptor);
    
    //Number of invented runs:
    //CategoryID = 6 - ships - have 1 run
    //GroupID = 330 - cloaks - have 1 run
    //GroupID = 773 - 782 - rigs - have 1 run
    if ($DEBUG) echo("Getting item stats<br/>");
    $stats=db_asocquery("SELECT it.`portionSize`,it.`groupID`,ig.`categoryID`,it.`typeID` FROM $LM_EVEDB.`invTypes` it JOIN $LM_EVEDB.`invGroups` ig ON it.`groupID`=ig.`groupID` WHERE `typeID`=$typeID;");
    $stats=$stats[0];
    
    $portionSize=$stats['portionSize'];
    if (!isset($portionSize)) $portionSize=1;
    //BPC Runs
    if ($stats['categoryID']==6 || $stats['groupID']==330 || ($stats['groupID'] >= 773 && $stats['groupID'] <= 782) ) {
        $bpcruns=1;
    } else {
        $bpcruns=10; //static 10 runs BPC for everything else
    }
    
//DECRYPTOR NUMBER OF RUNS MODIFICATION
    $bpcruns += $decryptor['runBonus'];
    
    if ($DEBUG) echo("Using $bpcruns BPC runs<br/>");
    
    if ($t2bpo['techLevel']==2) {
        if ($DEBUG) echo("This is tech II invention job, getting Tech I BPO stats<br/>");
        //for Tech II, find Tech I BPO
        $t1bptypeID=getT1BPOforT2BPO($bptypeID);
    } else if ($t2bpo['techLevel']==3) {
        if ($DEBUG) echo("This is tech III invention job, getting Relic stats<br/>");
        //for Tech III, must find a Relic
        $t1bptypeID=getRelicForT3BPC($bptypeID);
    }

    if ($t1bptypeID === FALSE) {
        if ($DEBUG) echo("Didn't find suitable Blueprint or Relic<br/>");
        return FALSE;
    }
    
    $invchance=$t1bptypeID['probability'];
    $t1bptypeID=$t1bptypeID['blueprintTypeID'];
    
//DECRYPTOR PROBABILITY MODIFICATION
    $invchance += $invchance * $decryptor['probabilityBonus'];
    
    //echo("t1bptypeID=".$t1bptypeID['blueprintTypeID']);
    if ($DEBUG) echo("Getting materials...<br/>");
    $extraMats=getBaseMaterials($t1bptypeID, 1, 0, 8);

    //form a complete material list
    if ($extraMats) {
        foreach ($extraMats as $mat) {
            $completeMats[$mat['typeID']]['qty']+=$mat['quantity'];
            $completeMats[$mat['typeID']]['typeName']=$mat['typeName'];
        }
    }
    
//DECRYPTOR ADD DECRYPTOR TO LIST
    if ($decryptor['decryptorTypeID'] > 0) {
        $completeMats[$decryptor['decryptorTypeID']]['qty']=1;
        $completeMats[$decryptor['decryptorTypeID']]['typeName']=$decryptor['typeName'];
    }
    
    if (count($completeMats)>0) {
        foreach ($completeMats as $id => $mat) {
            if ($unitPrice=getEveCentralPrice($id,$EC_PRICE_TO_USE_FOR_MAN['type'],$EC_PRICE_TO_USE_FOR_MAN['price'])) {             
                $returns['price']+=$mat['qty']*$unitPrice;
            } else {
                $returns['accurate']=false;
                //echo("Missing price: {$mat['typeName']}<br/>");
            }
        }
        //echo("DEBUG invchance=$invchance bpcruns=$bpcruns portionSize=$portionSize<br/>");
        $returns['price']=(($returns['price']/$invchance)/$bpcruns)/$portionSize;
        if ($DEBUG) echo("Returning materials (OK)<br/>");
        return $returns;
    } else {
        if ($DEBUG) echo("Didn't find any materials, returning FALSE (NOK)<br/>");
        return false;
    }
}

/**
 * Displays HTML table with manufacturing and invention quotes
 * 
 * @param int $typeID
 * @global string $DECIMAL_SEP - decimal point separator
 * @global string $THOUSAND_SEP - thousand separator
 */
function displayCosts($typeID) {
    global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP;
    $DEBUG=FALSE;
    //Manufacturing costs
    if ($DEBUG) echo('Getting Manufacturing Costs...<br/>');
    $mancost=calcManufacturingCost($typeID);
    if ($DEBUG) echo('Getting Invention Costs...<br/>');
    $invcost=calcInventionCost($typeID);
    if ($mancost || $invcost) {
        ?>		
        <table class="lmframework" style="width: 100%;">
        <tr><th colspan="3">Production cost estimation</th></tr>
        <?php if ($mancost) { ?>
        <tr><td><strong>Materials</strong></td><td><div title="This quote covers manufacturing cost of a single item."><?php echo(number_format($mancost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</div></td><td><div title="If LMeve has prices for all the ingredients, then the result will show as 'complete'. If one or more prices is missing, the result will show as 'prices missing'"><?php if ($mancost['accurate']) echo('Complete'); else echo('Some prices missing');  ?></div></td></tr>
        <?php }
        if ($invcost) { ?>
        <tr><td><strong>Invention</strong></td><td><div title="This quote covers invention cost of successful invention of a single T2 BPC, divided by the number of runs on this T2 BPC."><?php echo(number_format($invcost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</div></td><td><div title="If LMeve has prices for all the ingredients, then the result will show as 'complete'. If one or more prices is missing, the result will show as 'prices missing'"><?php if ($invcost['accurate']) echo('Complete'); else echo('Some prices missing');  ?></div></td></tr>
        <?php }
        $indexSystemID=getConfigItem('indexSystemID', '30000142');
        $indexData=db_asocquery("SELECT * FROM `crestindustrysystems` WHERE `solarSystemID`=$indexSystemID AND `activityID`=1;");
        $systemData=db_asocquery("SELECT `solarSystemName` FROM $LM_EVEDB.`mapSolarSystems` WHERE `solarSystemID`=$indexSystemID;");
        if (count($indexData)==1) {
            $systemIndex=$indexData[0]['costIndex'];
            if (count($systemData)==1) $systemName=$systemData[0]['solarSystemName']; else $systemName='Unknown';
            $npcquote="This quote is NPC manufacturing fee (introduced in Crius).\r\nCurrent Manufacturing Index for '$systemName' equals ".number_format($systemIndex, 4, $DECIMAL_SEP, $THOUSAND_SEP);
        } else {
            $systemIndex=sqrt(1/5431);
            $npcquote="This quote is NPC manufacturing fee (introduced in Crius).\r\nThis will differ between systems! Assuming average system cost index ".number_format($systemIndex, 4, $DECIMAL_SEP, $THOUSAND_SEP); 
        }
        ?>
        <tr><td><strong>Manufacturing</strong></td><td colspan="2"><div title="<?=$npcquote?>"><?php echo(number_format($systemIndex*$mancost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</div></td></tr>
        <tr><td><strong>Total</strong></td><td colspan="2"><div title="This quote is a sum of Materials and Invention quotes."><strong><?php echo(number_format($mancost['price']+$invcost['price']+$systemIndex*$mancost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</strong></div></td></tr>
        <?php
        if ($mancost['portionSize']>1) {
            ?>
        <tr><td colspan="3"><i><img src="<?=getUrl()?>ccp_icons/38_16_208.png" style="width: 16px; height: 16px; float: left;" /> Notice: minimum batch size: <?php echo($mancost['portionSize']); ?> items</i></td></tr>
            <?php
        }
        ?>
        </table>
        <?php  
    }
}

function calcTotalCosts($typeID) {
    global $LM_EVEDB, $DECIMAL_SEP, $THOUSAND_SEP;
    //get 'Crius' System Index
    $indexSystemID=getConfigItem('indexSystemID', '30000142');
        $indexData=db_asocquery("SELECT * FROM `crestindustrysystems` WHERE `solarSystemID`=$indexSystemID AND `activityID`=1;");
        if (count($indexData)==1) {
            $systemIndex=$indexData[0]['costIndex'];
        } else {
            $systemIndex=sqrt(1/5431);
        }
    //Manufacturing costs
    $mancost=calcManufacturingCost($typeID);
    $invcost=calcInventionCost($typeID);
    $npccost=$systemIndex*$mancost['price'];
    return $mancost['price']+$invcost['price']+$npccost;
}

function filterOresMakeup($ores_raw) {
    $ores = array();
    
    //$ores_raw = getRecycleMaterialsOres();
    
    foreach($ores_raw as $row) {
        //generic ore data
        $ores[$row['typeID']]['typeID']=$row['typeID'];
        $ores[$row['typeID']]['typeName']=$row['typeName'];
        $ores[$row['typeID']]['volume']=$row['volume'];
        $ores[$row['typeID']]['portionSize']=$row['portionSize'];
        $ores[$row['typeID']]['value'] += $row['quantity'] * $row['price'];
        //mineral data
        $ores[$row['typeID']]['minerals'][$row['mineralID']]['mineralID']=$row['mineralID'];
        $ores[$row['typeID']]['minerals'][$row['mineralID']]['mineralTypeName']=$row['mineralTypeName'];
        $ores[$row['typeID']]['minerals'][$row['mineralID']]['quantity']=$row['quantity'];
        $ores[$row['typeID']]['minerals'][$row['mineralID']]['price']=$row['price'];
    }
    
    return $ores;
}

function filterOresMinerals($ores_raw) {
    $minerals = array();
    
    //$ores_raw = getRecycleMaterialsOres();
    
    foreach($ores_raw as $row) {
        $minerals[$row['mineralID']]['mineralID']=$row['mineralID'];
        $minerals[$row['mineralID']]['mineralTypeName']=$row['mineralTypeName'];
    }
    
    ksort($minerals);
    
    return $minerals;
}

/**
 * Display ore value table - old style
 * @deprecated
 * @param type $ores
 * @param type $minerals
 */
function displayOreChartOld($ores, $minerals) {
    ?>
    <script type="text/javascript">
      $(document).ready(function() { 
          addTSCustomParsers();

          $("#minerals").tablesorter({ 
              headers: { 
                  0: { sorter: false },
                  4: { sorter: 'numsep' },
                  5: { sorter: 'numsep' },
                  6: { sorter: 'numsep' },
                  7: { sorter: 'numsep' },
                  8: { sorter: 'numsep' },
                  9: { sorter: 'numsep' },
                  10: { sorter: 'numsep' },
                  11: { sorter: 'numsep' },
                  12: { sorter: 'numsep' },
                  13: { sorter: 'numsep' },
                  14: { sorter: 'numsep' },
              } 
          }); 
      });
    </script>

    <table id="minerals" class="lmframework tablesorter" width="100%">
        <thead><tr>
            <th style="width: 32px; padding: 0px; text-align: center;">Icon</th><th>Ore name</th><th>Volume</th><th>Units/batch</th><th>Tritanium</th><th>Pyerite</th><th>Mexallon</th><th>Isogen</th><th>Nocxium</th><th>Zydrine</th><th>Megacyte</th><th>Morphite</th><th>ISK/batch</th><th>ISK/unit</th><th>ISK/m<sup>3</sup></th>
        </tr></thead>
        <tbody>
    <?php
        foreach($ores as $row) {
            $value=0;
            $tritanium=number_format($row['minerals'][34]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][34]['quantity']*$row['minerals'][34]['price'];
            $pyerite=number_format($row['minerals'][35]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][35]['quantity']*$row['minerals'][35]['price'];
            $mexallon=number_format($row['minerals'][36]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][36]['quantity']*$row['minerals'][36]['price'];
            $isogen=number_format($row['minerals'][37]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][37]['quantity']*$row['minerals'][37]['price'];
            $nocxium=number_format($row['minerals'][38]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][38]['quantity']*$row['minerals'][38]['price'];
            $zydrine=number_format($row['minerals'][39]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][39]['quantity']*$row['minerals'][39]['price'];
            $megacyte=number_format($row['minerals'][40]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][40]['quantity']*$row['minerals'][40]['price'];
            $morphite=number_format($row['minerals'][11399]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP); $value+=$row['minerals'][11399]['quantity']*$row['minerals'][11399]['price'];
            $iskunit=number_format($value/$row['portionSize'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
            $iskm3=number_format($value/$row['portionSize']/$row['volume'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
            $value=number_format($value, 2, $DECIMAL_SEP, $THOUSAND_SEP);
            ?>
            <tr>
                <td style="width: 32px; padding: 0px; text-align: center;">
            <?php itemhrefedit($row['typeID']); ?><img src="<?php echo(getTypeIDicon($row['typeID'])); ?>" title="<?php echo($row['typeName']); ?>" /></a>
                </td>
                <td><?php itemhrefedit($row['typeID']); echo($row['typeName']); ?></a></td>
                <td style="text-align: right;"><?php echo($row['volume']); ?></td>
                <td style="text-align: right;"><?php echo($row['portionSize']); ?></td>
                <td style="text-align: center;"><?php if($tritanium>0) echo($tritanium); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($pyerite>0) echo($pyerite); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($mexallon>0) echo($mexallon); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($isogen>0) echo($isogen); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($nocxium>0) echo($nocxium); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($zydrine>0) echo($zydrine); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($megacyte>0) echo($megacyte); else echo('-'); ?></td>
                <td style="text-align: center;"><?php if($morphite>0) echo($morphite); else echo('-'); ?></td>
                <td style="text-align: right;"><?php echo($value); ?></td>
                <td style="text-align: right;"><?php echo($iskunit); ?></td>
                <td style="text-align: right;"><?php echo($iskm3); ?></td>
            </tr>
            <?php
        }
    ?>
            </tbody>
    </table>
    <?php
}

/**
 * Display ore value table - new style
 * @param type $ores
 * @param type $minerals
 */
function displayOreChart($ores, $minerals) {
    ?>
    <script type="text/javascript">
      $(document).ready(function() { 
          addTSCustomParsers();

          $("#minerals").tablesorter({ 
              headers: { 
                  0: { sorter: false },
<?php
                    for ($i = 4; $i < 4 + count($minerals); $i++) {
                        echo("                  $i: { sorter: 'numsep' },\r\n");
                    }
?>
              } 
          }); 
      });
    </script>
    <table id="minerals" class="lmframework tablesorter" width="100%">
        <thead>
            <tr>
                <th style="width: 32px; padding: 0px; text-align: center;" class="stickyheader">Icon</th>
                <th class="stickyheader">Ore name</th>
                <th class="stickyheader">Volume</th>
                <th class="stickyheader">Units/batch</th>
                <th class="stickyheader">ISK/batch</th>
                <th class="stickyheader">ISK/unit</th>
                <th class="stickyheader">ISK/m<sup>3</sup></th>
                <?php foreach($minerals as $mineral) {
                    echo("<th class=\"stickyheader\"><img src=\"" . getTypeIDicon($mineral['mineralID']) . "\" alt=\"".$mineral['mineralTypeName']."\" title=\"".$mineral['mineralTypeName']."\">");
                } ?>
            </tr>
        </thead>
        <tbody>
    <?php
    /******* THIS LOOP IS PROCESSING HEAVY - 33s *******/
        foreach($ores as $row) {
            $value=0;

            $iskunit=number_format($row['value']/$row['portionSize'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
            $iskm3=number_format($row['value']/$row['portionSize']/$row['volume'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
            $value=number_format($row['value'], 2, $DECIMAL_SEP, $THOUSAND_SEP);
            ?>
            <tr>
                <td style="width: 32px; padding: 0px; text-align: center;">
            <?php itemhrefedit($row['typeID']); ?><img src="<?php echo(getTypeIDicon($row['typeID'])); ?>" title="<?php echo($row['typeName']); ?>" /></a>
                </td>
                <td><?php itemhrefedit($row['typeID']); echo($row['typeName']); ?></a></td>
                <td style="text-align: right;"><?php echo($row['volume']); ?></td>
                <td style="text-align: right;"><?php echo($row['portionSize']); ?></td>
                <td style="text-align: right;"><?php echo($value); ?></td>
                <td style="text-align: right;"><?php echo($iskunit); ?></td>
                <td style="text-align: right;"><?php echo($iskm3); ?></td>
                <?php
                foreach($minerals as $mineral) {
                    ?><td style="text-align: center;"><?php if($row['minerals'][$mineral['mineralID']] > 0) echo(number_format($row['minerals'][$mineral['mineralID']]['quantity'], 0, $DECIMAL_SEP, $THOUSAND_SEP)); else echo('-'); ?></td><?php
                }
                ?>
                
            </tr>
            <?php
        }
        
    ?>
            </tbody>
    </table>
    <script>
        $(() => $('#minerals').floatThead());
    </script>
    <?php
    }

    function updateMineralsMarketConfig() {
        global $LM_EVEDB;
        
        $ores_raw = getRecycleMaterialsOres();
        
        $minerals = filterOresMinerals($ores_raw);
        
        $mineralIDs = array_keys($minerals);
                
        //add mineral prices to market configuration
        $sql = "INSERT IGNORE INTO `cfgmarket` SELECT typeID FROM `$LM_EVEDB`.invTypes WHERE typeID IN (" . implode(',', $mineralIDs) . ")";
        
        return db_uquery($sql);
    }
    
    function updateOrePriceByComposition() {
        $ores_raw = getRecycleMaterialsOres();
        $ores = filterOresMakeup($ores_raw);
        
        $reprocessingYield = getConfigItem('reprocessingYield', 0.6957);
        
        foreach ($ores as $typeID => $ore) {
            $v = $reprocessingYield * $ore['value'] / $ore['portionSize'];
            db_uquery("DELETE FROM `apiprices` WHERE `typeID` = $typeID;");
            db_uquery("INSERT INTO `apiprices` VALUES ($typeID, 0, $v, $v, $v, 0, $v, 0.00, 'sell'),($typeID, 0, $v, $v, $v, 0, $v, 0.00, 'buy')");
        }
    }


?>
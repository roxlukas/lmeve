<?php
//Blueprint and material related functions

/**
 * Finds blueprint typeID for product typeID
 * 
 * @global type $LM_EVEDB - static data dump schema
 * @param $typeID - blueprint typeID
 */
function getBlueprintByProduct($typeID) {
	global $LM_EVEDB;
	$blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invblueprinttypes` WHERE `productTypeID` = $typeID;");
	//$techLevel=$blueprint[0][4];
	//$wasteFactor=$blueprint[0][11]/100;
	if (count($blueprint)==1) {
            return $blueprint[0];
	} else { //blueprint not found... maybe given typeID is a blueprint itself??
            $blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invblueprinttypes` WHERE `blueprintTypeID` = $typeID;");
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
        $blueprint=db_asocquery("SELECT t1.* FROM $LM_EVEDB.`invblueprinttypes` t1 
                JOIN $LM_EVEDB.`invmetatypes` imt
                ON t1.`productTypeID`=imt.`parentTypeID`
                JOIN $LM_EVEDB.`invblueprinttypes` t2
                ON imt.`typeID`=t2.`productTypeID`
                WHERE t2.`blueprintTypeID` = $typeID
                AND t2.`techLevel`=2;");
        if (count($blueprint)==1) {
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
    $portionSize=db_asocquery("SELECT `portionSize` FROM $LM_EVEDB.`invtypes` WHERE `typeID`=$typeID");
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
	$blueprint=db_asocquery("SELECT * FROM $LM_EVEDB.`invblueprinttypes` WHERE `blueprintTypeID` = $typeID;");
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
		FROM $LM_EVEDB.`invtypematerials` AS mat
		JOIN $LM_EVEDB.`invtypes` AS inv
		ON mat.materialTypeID = inv.typeID
		WHERE mat.typeID=$typeID";
	$recycle=db_asocquery($sql);
	if (count($recycle)>0) {
		return $recycle;
	} else {
		return FALSE;
	}	
}

//getBaseMaterials($typeID) - get recyclable (base) materials for typeID
//$typeID - ITEM typeID
function getBaseMaterials($typeID,$runs=1,$melvl_override=null) {
        //echo("getBaseMaterials() DEBUG: typeID='$typeID', runs='$runs' <br/>");
	global $LM_EVEDB;
	$recycle=getRecycleMaterials($typeID);
	$bpo=getBlueprintByProduct($typeID);
        
	//echo('DEBUG: $bpo=');var_dump($bpo);echo('<br>');
	$materials=getExtraMats($typeID,1); //get extra mats for manufacturing (activityID = 1)
	//echo('DEBUG: $materials=');var_dump($materials);echo('<br>');
	
	$techLevel=$bpo['techLevel'];
	
	if ($recycle!=false) {
			//echo("DEBUG: count(\$recycle)>0<br>");
			if ($techLevel==2) { //Subtract T1 materials from T2 recycle for Tech II
				//echo("DEBUG: Tech Level==2<br>");

				$tech1itemID=0;
                                if ($materials) {
                                    foreach ($materials as $row) {
                                            //echo("DEBUG: Looking for recycle material, typeID=${row['typeID']}, recycle=${row['recycle']}<br>");
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
                                        if (!isset($melevel)) $melevel=-4;
                                        if (!isset($pelevel)) $pelevel=-4;
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
                        $wasteFactor=getWasteFactor($typeID);
                        if ($melevel>=0) {
                            $multiplier=1+($wasteFactor/(1 + $melevel));
                            $waste=$wasteFactor/(1 + $melevel)*100;
                        } else {
                            $multiplier=1+($wasteFactor*(1 - $melevel));
                            $waste=$wasteFactor*(1 - $melevel)*100;
                        }
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
		printf("<strong>Waste factor:</strong> %4.2f%%",$waste);
		
		echo("<table class=\"lmframework\" width=\"100%\">");
		echo("<tr colspan=\"2\"><th>Material</th><th>Quantity</th></tr>");
		//draw Material list
		foreach ($recycle as $row) {
			//$notperfect=round($row['quantity']*$multiplier);
                        $notperfect=$row['notperfect'];
			echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>${notperfect} (perfect: ${row['quantity']})</td></tr>");
		}
		echo("</table>");
	}
	return;
}

//getSkills($typeID,$activityID) - get skills for typeID and activityID
//$typeID - ITEM typeID
//$activityID - ID of activity: 1-Manufacturing 5-Copying 8-Invention, etc.
function getSkills($typeID,$activityID) {
	global $LM_EVEDB;
	$sql="SELECT itp.typeName, mat.quantity, mat.damagePerJob, itp.typeID
		FROM $LM_EVEDB.`ramtyperequirements` AS mat
		JOIN $LM_EVEDB.`invtypes` AS itp
		ON mat.requiredTypeID = itp.typeID
		JOIN $LM_EVEDB.`invgroups` AS igr
		ON itp.groupID=igr.groupID
		WHERE mat.typeID=$typeID
		AND igr.categoryID = 16
		AND mat.activityID = $activityID";
	$skills=db_asocquery($sql); //Skills
	if (count($skills)>0) {
		return $skills;
	} else {
		return FALSE;
	}
}

function displaySkills($skills) {
	if ($skills!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"2\"><th>Skill</th><th>Required level</th></tr>");
			foreach ($skills as $row) {
				$row['damagePerJob']=sprintf("%d%%",$row['damagePerJob']*100);
				if ($row['quantity']>0)	echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>${row['quantity']}</td></tr>");
			}
			echo("</table>");
		}
	return;
}

/**
 * Get extra materials for typeID and activityID
 * 
 * @global type $LM_EVEDB
 * @param type $typeID - typeID of item in question
 * @param type $activityID - ID of activity: 1-Manufacturing 5-Copying 8-Invention, etc.
 * @param type $runs - how many production runs? (default = 1)
 * @return mixed array with materials or false if there are none
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
		FROM $LM_EVEDB.`ramtyperequirements` AS mat
		JOIN $LM_EVEDB.`invtypes` AS itp
		ON mat.requiredTypeID = itp.typeID
		JOIN $LM_EVEDB.`invgroups` AS igr
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

function displayExtraMats($materials) {
	if ($materials!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"3\"><th>Extra Material</th><th>Quantity</th><th>dmg per job</th></tr>");
			foreach ($materials as $row) {
				$row['damagePerJob']=sprintf("%d%%",$row['damagePerJob']*100);
				if ($row['quantity']>0) echo("<tr colspan=\"3\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>${row['quantity']}</td><td>${row['damagePerJob']}</td></tr>");
			}
			echo("</table>");
		}
	return;
}

/**
 * Draw a HTML table with kit data (kit is a complete set of ingredients for a given amount of industry jobs)
 * 
 * @param type $recycle - base materials
 * @param type $materials - extra materials
 * @param type $melevel - ME level
 * @param type $wasteFactor - waste factor
 * @deprecated
 */
function displayKit($recycle,$materials,$melevel,$wasteFactor) { //OLD!!
    if ($materials!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"2\"><th style=\"width: 67%\">Extra Materials</th><th>Quantity</th></tr>");
			foreach ($materials as $row) {
                            //data interface workaround
                            if (strpos($row['typeName'],'Data Interface')!==false) $row['quantity']=1;
				//$row['damagePerJob']=sprintf("%d%%",$row['damagePerJob']*100);
				if ($row['quantity']>0) echo("<tr colspan=\"3\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>".$row['quantity']*$row['damagePerJob']."</td></tr>");
			}
			echo("</table>");
    }
    if ($recycle!=false) {
		/*if ($melevel>=0) {
			$multiplier=1+($wasteFactor/(1 + $melevel));
			$waste=$wasteFactor/(1 + $melevel)*100;
		} else {
			$multiplier=1+($wasteFactor*(1 - $melevel));
			$waste=$wasteFactor*(1 - $melevel)*100;
		}*/
	
		//printf("<strong>Waste factor:</strong> %4.2f%%",$waste);
		
		echo("<table class=\"lmframework\" width=\"100%\">");
		echo("<tr colspan=\"2\"><th style=\"width: 67%\">Materials</th><th>Quantity</th></tr>");
		//draw Material list
		foreach ($recycle as $row) {
			//$notperfect=round($row['quantity']*$multiplier);
                        $notperfect=$row['notperfect'];
			echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>${notperfect}</td></tr>");
		}
		echo("</table>");
	}
}

/**
 * Draw a HTML table with kit data (kit is a complete set of ingredients for a given amount of industry jobs)
 * 
 * @param type $recycle - base materials
 * @param type $materials - extra materials
 * @param type $melevel - ME level
 * @param type $wasteFactor - waste factor
 */
function displayKit2($recycle,$materials,$melevel,$wasteFactor,$location) { //NEW!
    if ($location) {
        echo("<table class=\"lmframework\" width=\"100%\">");
	//echo("<tr><th colspan=\"2\" style=\"width: 100%\">Mobile Laboratory / Assembly Array</th></tr>");
        echo("<tr><th colspan=\"2\" style=\"width: 100%\">Location</th></tr>");
        echo("<tr><td style=\"padding: 0px; width: 32px;\"><img src=\"ccp_img/${location['structureTypeID']}_32.png\" title=\"${location['structureTypeName']}\"></td><td style=\"width: 95%;\"><strong>${location['structureName']}</strong><br/>${location['moonName']}</td></tr>");
	echo("</table>");		
    }
    if ($materials!=false) {
			echo("<table class=\"lmframework\" width=\"100%\">");
			echo("<tr colspan=\"2\"><th style=\"width: 67%\">Extra Materials</th><th>Quantity</th></tr>");
			foreach ($materials as $row) {
                            //data interface workaround
                            if (strpos($row['typeName'],'Data Interface')!==false) $row['quantity']=1;
				//$row['damagePerJob']=sprintf("%d%%",$row['damagePerJob']*100);
				if ($row['quantity']>0) echo("<tr colspan=\"3\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>".$row['quantity']*$row['damagePerJob']."</td></tr>");
			}
			echo("</table>");
    }
    if ($recycle!=false) {
		/*if ($melevel>=0) {
			$multiplier=1+($wasteFactor/(1 + $melevel));
			$waste=$wasteFactor/(1 + $melevel)*100;
		} else {
			$multiplier=1+($wasteFactor*(1 - $melevel));
			$waste=$wasteFactor*(1 - $melevel)*100;
		}*/
	
		//printf("<strong>Waste factor:</strong> %4.2f%%",$waste);
		
		echo("<table class=\"lmframework\" width=\"100%\">");
		echo("<tr colspan=\"2\"><th style=\"width: 67%\">Materials</th><th>Quantity</th></tr>");
		//draw Material list
		foreach ($recycle as $row) {
			//$notperfect=round($row['quantity']*$multiplier);
                        $notperfect=$row['notperfect'];
			echo("<tr colspan=\"2\"><td><a href=\"?id=10&id2=1&nr=${row['typeID']}\"><img src=\"ccp_img/${row['typeID']}_32.png\" style=\"width: 16px; height: 16px; float: left;\" /> ${row['typeName']}</a></td><td>${notperfect}</td></tr>");
		}
		echo("</table>");
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
                        //echo("min: ${row['min']} <br/>");
                        return $row['min'];
                        break;
                    case 'max':
                        //echo("max: ${row['max']} <br/>");
                        return $row['max'];
                        break;
                    case 'avg':
                        //echo("avg: ${row['avg']} <br/>");
                        return $row['avg'];
                        break;
                    case 'median':
                        //echo("median: ${row['median']} <br/>");
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
    $returns=array();
    $returns['price']=0;
    $returns['accurate']=true;
    
    $baseMats=getBaseMaterials($typeID);
    $extraMats=getExtraMats($typeID, 1);
    $techLevel=getTechLevel($typeID);
    
    //ME and PE settings
    if ($mepe=getMEPE($typeID)) {
        $melevel=$mepe['me'];
        $pelevel=$mepe['pe'];
    }
    switch ($techLevel) {
        case 2:
            if (!isset($melevel)) $melevel=-4;
            break;
	case 3:
            if (!isset($melevel)) $melevel=0;
            break;
	default:
            if (!isset($melevel)) $melevel=0;
    }
    //echo("DEBUG: ME level = $melevel<br/>");  
    
    //Waste factors
    $baseWaste=getWasteFactor($typeID);
    if ($melevel>=0) {
	$multiplier=1+($baseWaste/(1 + $melevel));
    } else {
	$multiplier=1+($baseWaste*(1 - $melevel));
    }
    
    //form a complete material list
    if($baseMats) {
        foreach ($baseMats as $mat) {
            //echo("${mat['typeName']} = ${mat['quantity']} * $multiplier = ".$mat['quantity']*$multiplier."<br/>");
            $completeMats[$mat['typeID']]['qty']+=$mat['quantity']*$multiplier;
            $completeMats[$mat['typeID']]['typeName']=$mat['typeName'];
        }
    }
    if ($extraMats) {
        foreach ($extraMats as $mat) {
            $completeMats[$mat['typeID']]['qty']+=$mat['quantity']*$mat['damagePerJob'];
            $completeMats[$mat['typeID']]['typeName']=$mat['typeName'];
        }
    }
    //now that we have a complete list of materials, we can try to calculate the price
    //echo("count=".count($completeMats)."<br/>");
    //var_dump($completeMats);
    if (count($completeMats)>0) {
        foreach ($completeMats as $id => $mat) {
            if (getBlueprintByProduct($id)) {
                $subcost=calcManufacturingCost($id);
                $returns['price']+=$mat['qty']*$subcost['price'];
                $returns['accurate']=$subcost['accurate']&&$returns['accurate'];
            } else {
                if ($unitPrice=getEveCentralPrice($id,'sell','min')) {  
                    //echo("$id - $unitPrice<br/>");
                    $returns['price']+=$mat['qty']*$unitPrice;
                } else {
                    $returns['accurate']=false;
                    //echo("Missing price: ${mat['typeName']}<br/>");
                }
            }
        }
        return $returns;
    } else {
        return false;
    }
}

/**
 * Calculates invention cost for a given typeID
 * 
 * @param type $typeID - typeID of the item (product) in question
 * @return array $return['quote'] (float) - calculated quote; $return['accurate'] (boolean) - is the price accurate?
 */
function calcInventionCost($typeID) {
    global $LM_EVEDB;
    $returns=array();
    $returns['price']=0;
    $returns['accurate']=true;
    if ($t2bpo=getBlueprintByProduct($typeID)){
        $bptypeID=$t2bpo['blueprintTypeID']; //switch from item ID to Tech 2 BPO ID
    } else {
        return false;
    }
    //now check if we are dealing with Tech II at all
    if ($t2bpo['techLevel']!=2) return false;
    
    //Number of invented runs:
    //CategoryID = 6 - ships - have 1 run
    //GroupID = 330 - cloaks - have 1 run
    //GroupID = 773 - 782 - rigs - have 1 run
    $stats=db_asocquery("SELECT it.`groupID`,ig.`categoryID`,it.`typeID` FROM $LM_EVEDB.`invtypes` it JOIN $LM_EVEDB.`invgroups` ig ON it.`groupID`=ig.`groupID` WHERE `typeID`=$typeID;");
    $stats=$stats[0];
    //BPC Runs
    if ($stats['categoryID']==6 || $stats['groupID']==330 || ($stats['groupID'] >= 773 && $stats['groupID'] <= 782) ) {
        $bpcruns=1;
    } else {
        $bpcruns=10; //static 10 runs BPC for everything else
    }
    //Invention chances
    if ($stats['categoryID']==7 || $stats['categoryID']==8 || $stats['categoryID']==18) {
        $invchance=0.4;
    } else if ($stats['groupID']==324 || $stats['groupID']==831 || $stats['groupID']==834 || $stats['groupID']==830 || $stats['groupID']==541 || $stats['groupID']==902 || $stats['groupID']==893 || $stats['typeID']==22546) {
        //frigates, destroyers, freighters, skiff
        //AF - 324, Cep - 831, SB - 834, Cov - 830
        //Dic - 541
        //JF - 902
        //EAF - 893
        $invchance=0.3;
    } else if ($stats['groupID']==1202 || $stats['groupID']==380 || $stats['groupID']==906 || $stats['groupID']==358 || $stats['groupID']==833 || $stats['groupID']==894 || $stats['typeID']==22548) {
        //crusier, industrials, mackinaw
        //Blockade runner - 1202
        //DST - 380
        //Combat Recon - 906
        //HAC - 358
        //Force Recon - 833
        //HIC - 894
        $invchance=0.25;
    } else if ($stats['groupID']==540 || $stats['groupID']==898 || $stats['groupID']==900 || $stats['typeID']==22544) {
        //battlecruisers, battlships, hulk
        //CS - 540
        //BO - 898, Marauder - 900
        $invchance=0.2;
    } 
    
    $extraMats=getExtraMats($bptypeID, 8);
    //form a complete material list
    if ($extraMats) {
        foreach ($extraMats as $mat) {
            //Data Interface workaround
            if (strpos($mat['typeName'],'Data Interface')===false) {
                $completeMats[$mat['typeID']]['qty']+=$mat['quantity']*$mat['damagePerJob'];
                $completeMats[$mat['typeID']]['typeName']=$mat['typeName'];
            }
        }
    }
    if (count($completeMats)>0) {
        foreach ($completeMats as $id => $mat) {
            if ($unitPrice=getEveCentralPrice($id,'sell','min')) {             
                $returns['price']+=$mat['qty']*$unitPrice;
            } else {
                $returns['accurate']=false;
                //echo("Missing price: ${mat['typeName']}<br/>");
            }
        }
        $returns['price']=($returns['price']/$invchance)/$bpcruns;
        return $returns;
    } else {
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
    global $DECIMAL_SEP, $THOUSAND_SEP;
    //Manufacturing costs
    $mancost=calcManufacturingCost($typeID);
    $invcost=calcInventionCost($typeID);
    if ($mancost || $invcost) {
        ?>		
        <table class="lmframework" style="width: 100%;">
        <tr><th colspan="3">Production cost estimation</th></tr>
        <?php if ($mancost) { ?>
        <tr><td><strong>Manufacturing</strong></td><td><div title="This quote covers manufacturing cost of a single item."><?php echo(number_format($mancost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</div></td><td><div title="If LMeve has prices for all the ingredients, then the result will show as 'complete'. If one or more prices is missing, the result will show as 'prices missing'"><?php if ($mancost['accurate']) echo('Complete'); else echo('Some prices missing');  ?></div></td></tr>
        <?php }
        if ($invcost) { ?>
        <tr><td><!--<img title="Invention cost formula correctly assumes number of runs on the BPC, but uses a hardcoded 50% invention chance. Accurate invention chance will be implemented soon." src="img/exc.gif" /> --><strong>Invention</strong></td><td><div title="This quote covers invention cost of successful invention of a single T2 BPC, divided by the number of runs on this T2 BPC."><?php echo(number_format($invcost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</div></td><td><div title="If LMeve has prices for all the ingredients, then the result will show as 'complete'. If one or more prices is missing, the result will show as 'prices missing'"><?php if ($invcost['accurate']) echo('Complete'); else echo('Some prices missing');  ?></div></td></tr>
        <?php } ?>
        <tr><td><strong>Total</strong></td><td colspan="2"><div title="This quote is a sum of Manufacturing and Invention quotes."><strong><?php echo(number_format($mancost['price']+$invcost['price'], 2, $DECIMAL_SEP, $THOUSAND_SEP)); ?> ISK</strong></div></td></tr>
        </table>
        <?php  
    }
}
?>
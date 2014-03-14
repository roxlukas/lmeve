<?php
/**
 * Displays a HTML percent bar for single value.
 * 
 * @param type $percentage - percent value
 * @param type $title - percent bar hint / title
 * @return type 
 */
function percentbar($percentage,$title) {
    if ($percentage<=100) $dispperc=$percentage; else $dispperc=100;
    $percleft=100-$dispperc;
    //echo("DEBUG dispperc=$dispperc percleft=$percleft title=$title");
    echo("<div class=\"percent\"><div style=\"width: ${dispperc}px;\" class=\"percentdone\"></div>
    <div style=\"width: 100px;\" class=\"percentnum\" title=\"$title\">$percentage%</div></div>");
    return true;
}

/**
 * Displays a HTML percent bar for two values.
 * 
 *  ______ - value 1
 * [----==  ]
 *      ^^ - value 2
 * 
 * @param integer $percentage1 - percent value 1 (bigger, whole value)
 * @param integer $percentage2 - percent value 2 (lower, recent change)
 * @param string $title - percent bar hint / title
 * @return type 
 */
function percentbar2($percentage1,$percentage2,$title) {
    if ($percentage1<=100) $dispperc1=$percentage1; else $dispperc1=100;
    if ($percentage2<=100) $dispperc2=$percentage2; else $dispperc2=100;
    
    if ($dispperc2<$dispperc1) {
        $darker=$dispperc1-$dispperc2;
        $lighter=$dispperc1;
    } else {
        $darker=0;
        $lighter=$dispperc1;
    }
    echo("<div class=\"percent\"><div style=\"width: ${lighter}px;\" class=\"percentrecent\"><div style=\"width: ${darker}px;\" class=\"percentdone\"></div></div>
    <div style=\"width: 100px;\" class=\"percentnum\" title=\"$title\">$percentage1%</div></div>");
    return true;
}

/*******************************************************************************************************/

/**
 * Displays a HTML percent bar for single value.
 * OLD - deprecated
 * 
 * @deprecated
 * @param type $percentage - percent value
 * @param type $title - percent bar hint / title
 * @return type 
 */
function percentbar_old($percentage,$title) {
    if ($percentage<=100) $dispperc=$percentage; else $dispperc=100;
    $percleft=100-$dispperc;
    //echo("DEBUG dispperc=$dispperc percleft=$percleft title=$title");
    echo("<table class=\"percentbar\">
    <tr><td width=\"$dispperc%\" class=\"percentdone\"><td width=\"$percleft%\" class=\"percent\"></td></tr></table>
    <table width=\"100\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"percentnum\">
    <tr><td width=\"100%\" colspan=\"2\" class=\"percentnum\"><span title=\"$title\">$percentage%</span></td></tr></table>");
    return true;
}

/**
 * Displays a HTML percent bar for two values. OLD - Deprecated.
 * 
 *  ______ - value 1
 * [----==  ]
 *      ^^ - value 2
 * 
 * @deprecated
 * @param integer $percentage1 - percent value 1 (bigger, whole value)
 * @param integer $percentage2 - percent value 2 (lower, recent change)
 * @param string $title - percent bar hint / title
 * @return type 
 */
function percentbar2_old($percentage1,$percentage2,$title) {
    //echo("DEBUG percentage1=$percentage1, percentage2=$percentage2");
    if ($percentage1<=100) $dispperc1=$percentage1; else $dispperc1=100;
    if ($percentage2<=100) $dispperc2=$percentage2; else $dispperc2=100;
    if ($dispperc1 > $dispperc2) {
        $dispperc1-=$dispperc2;
        $dispperc2=$dispperc2;
    } else {
        $dispperc1=0;
        $dispperc2=$dispperc2;
    }
    $percleft=100-($dispperc1+$dispperc2);
    //echo("DEBUG dispperc=$dispperc percleft=$percleft title=$title");
    echo("<table class=\"percentbar\">
    <tr><td width=\"$dispperc1%\" class=\"percentdone\"><td width=\"$dispperc2%\" class=\"percentrecent\"><td width=\"$percleft%\" class=\"percent\"></td></tr></table>
    <table width=\"100\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"percentnum\">
    <tr><td width=\"100%\" colspan=\"2\" class=\"percentnum\"><span title=\"$title\">$percentage1%</span></td></tr></table>");
    return true;
}
?>

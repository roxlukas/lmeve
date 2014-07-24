<?php
if ((isset($granted))&&($granted>-1)) {

/*
foreach($user as $nr => $login) {
	$sql="UPDATE admin SET login='$login', pass='$pass[$nr]', lastip='$lastip[$nr]', last='$last[$nr]', prefs1=$prefs1[$nr], prefs2=$prefs2[$nr], prefs3='$prefs3[$nr]', prefs4=$prefs4[$nr], sudo=$sudo[$nr], log=$log[$nr] WHERE id=$nr;";
	db_uquery($sql);	
}
*/

} else {
	include('lang.php');
	echo($LOGIN_AGAIN);
}
?>

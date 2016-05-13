<?php
	checksession(); //check if we are called by a valid session
	$LOCKFILE="../var/poller.lock";
	if (file_exists($LOCKFILE) && ((time()-filemtime($LOCKFILE)) > 900)) {
		echo('<div class="newmsg"><img src="'.getUrl().'img/exc.gif" alt="Warning" /> EVE API Poller has hanged, contact Administrator!');
		if (checkrights("Administrator")) {
			echo('<button type="button" onclick="location.href=\'index.php?id=5&id2=9\'">Reset poller</button>');
		}
		echo('</div>');
	}
?>
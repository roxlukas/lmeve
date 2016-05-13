<?php
	checksession(); //check if we are called by a valid session
        $lockfile=__DIR__.'/../INSTALL';
	if (file_exists($lockfile)) {
		echo('<div class="newmsg"><img src="'.getUrl().'img/exc.gif" alt="Warning" /> Installation complete. Please delete the \'INSTALL\' file in the LMeve root directory.');
		echo('</div>');
	}
        
?>
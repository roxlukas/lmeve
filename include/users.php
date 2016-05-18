<?php

function showUsers($admini) {
    //$token=rand(111111111,999999999);
    global $LM_SALT;
    $token=md5($LM_SALT.serialize($admini));
    ?>
  
    <table class="lmframework tablesorter" id="users_<?php echo($token); ?>" width="422">
	    <thead><tr><th width="100">
	    Login
	    </th><th width="80">
	    Last IP
	    </th><th>
	    Enabled
	    </th><th width="120">
	    Recently active
	    </th><th width="16">
	    ---
	    </th></tr></thead>
<?php
    foreach ($admini as $admin) {
			$tmp=explode(' ',$admin['last']);
			$tmpd=explode('.',$tmp[0]);
			$tmp_data=sprintf('%d-%d-%d %s',$tmpd[2],$tmpd[1],$tmpd[0],$tmp[1]);
			$t0 = strtotime($tmp_data);
			$t2 = time();
			$dt=$t2-$t0;
			if ($dt < 300) {
				$log=' class="tab-act"';
			} else {
				$log='';
			}
	    echo("<tr>");
   	    echo("<td$log>");
		if ($admin['act']=="0") echo('<font color="#a0a0a0">');
	    if (checkrights("Administrator,EditUsers")) {
			echo("<a href=\"?id=7&id2=2&nr=${admin['userID']}\">${admin['login']}</a>");
		} else {
			echo($admin['login']);
		}
	    echo('</td>');
	    echo("<td$log>");
	    	if ($admin['act']=="0") echo('<font color="#a0a0a0">');
	    echo($admin['lastip']);
	    echo('</td>');
	    echo("<td$log>");
	    	if ($admin['act']=="0") echo('<font color="#a0a0a0">');
                if ($admin['act']=="1") echo('Enabled'); else echo('Disabled');
	    echo('</td>');
	    echo("<td$log>");
	    	if ($admin['act']=="0") echo('<font color="#a0a0a0">');
	    echo($admin['last']);
	    echo('</td>');
	    echo("<td$log>");
	    echo('<a href="?id=4&id2=1&nr=new&adr=');
	    echo($admin['userID']);
	    echo('" title="Send message"><img src="'.getUrl().'img/msg.gif" alt="MSG"></a>');
	    echo('</td></tr>');
	}?>
        </table>
<script type="text/javascript">
        $("#users_<?php echo($token); ?>").tablesorter({ 
            headers: { 4: { sorter: false } } 
        });
</script>
        <?php
}
?>

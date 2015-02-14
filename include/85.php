<?php 
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewCDNStats")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>${LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='CDN Proxy Statistics'; //Panel name (optional)
//standard header ends here

global $LM_EVEDB, $LM_CCPWGL_URL, $LM_CCPWGL_USEPROXY, $LM_CCPWGL_CACHESCHEMA;

include_once('stats.php');
$width=600;

$date=secureGETnum("date");

if (strlen($date)==6) {
	$year=substr($date,0,4);
	$month=substr($date,4,2);
} else {
	$year=date("Y");
	$month=date("m");	
}
?>

        <a name="top"></a>
            <div class="tytul">
                CDN Proxy Statistics for <?php echo("$year-$month"); ?><br>
            </div>
            <a href="#down">Scroll down</a>
            <div class="tekst">
            <?php
                showVisitorsMonthly($year,$month,getVisitorsMonthly($year, $month));
            ?>
            
            </div>
                <div id="global-cdn-info" style="overflow: hidden;">
                    <div id="settings" style="float: left;">
                        <h3>CDN Proxy enabled: <a href="#"><?=$LM_CCPWGL_USEPROXY?'YES':'NO'?></a><br/>CDN Origin address: <a href="<?=$LM_CCPWGL_URL?>"><?=$LM_CCPWGL_URL?></a></h3>
                    </div>
                    <div id="cdn-cache-size" style="float: left; margin-left: 16px;">
                        Checking cache size...
                    </div>
                </div>
                <div id="stats">
                        <div id="wrapper" style="overflow: hidden;">
                            <div id="col1" style="float: left;">
                                <?php  ?>
                            </div>
                            <div id="col2" style="float: left;">
                                <?php  ?>
                            </div>
                        </div>
                </div>
                <div class="tytul">
                    Last 24 hours
                </div>
                <div id="cache_stats">
                        Checking cache hit ratio...
                </div>
                <div id="realtime">
                        Checking statistics...
                </div>
                <script type="text/javascript">
                    ajax_get('ajax.php?act=GET_PROXY_STATS','realtime');
                    setInterval(function(){ 
                        ajax_get('ajax.php?act=GET_PROXY_STATS','realtime');
                        ajax_get('ajax.php?act=GET_CACHE_SIZE','cdn-cache-size'); }, 10000);
                    ajax_get('ajax.php?act=GET_CACHE_STATS','cache_stats');
                    setInterval(function(){ ajax_get('ajax.php?act=GET_CACHE_STATS','cache_stats'); }, 3000);
                    ajax_get('ajax.php?act=GET_CACHE_SIZE','cdn-cache-size');
                </script>
		
		<div class="tekst">
			<a href="#top">Scroll up</a>
			<a name="down"></a>
			
		    </div><br>
		

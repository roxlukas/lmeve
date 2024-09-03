<?php
//standard header for each included file
checksession(); //check if we are called by a valid session
if (!checkrights("Administrator,ViewOreValues")) { //"Administrator,ViewOverview"
	global $LANG;
	echo("<h2>{$LANG['NORIGHTS']}</h2>");
	return;
}
$MENUITEM=0; //Panel ID in menu. Used in hyperlinks
$PANELNAME='Ore values table'; //Panel name (optional)
//standard header ends here

generate_meta('Ore Values Chart shows mineral composition and current value of all ores available in EVE Online.', generate_title('Ore Chart'));

?>
<div class="tytul">
    <?php echo($PANELNAME); ?><br>
</div>

<script type="text/javascript">
    hideLeftPanel();
</script>

<div id="pageContents"><em><img src="<?=getUrl()?>img/loader.png" /> Loading...</em></div>
<script type="text/javascript">
    ajax_get('<?=getUrl()?>ajax.php?act=CACHE&page=a7','pageContents');
</script>
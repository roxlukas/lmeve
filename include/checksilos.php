<?php
	checksession(); //check if we are called by a valid session
        include_once('inventory.php');
	$silos = getSilos();
        foreach ($silos as $silo) {
                if ($silo['filledPercent'] >= getConfigItem('siloPercentage',90)) {
                    ?>
                    <div class="newmsg">
                        <img src="<?=getUrl()?>img/exc.gif" alt="Warning" />
                        <?=$silo['siloTypeName']?> in '<?=$silo['locationName']?>' with <?=$silo['contentTypeName']?>
                        is <?=$silo['filledPercent']?>% full
                    </div>
                    <?php
                }
        }
        
?>
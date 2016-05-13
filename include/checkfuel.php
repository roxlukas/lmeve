<?php
	checksession(); //check if we are called by a valid session
        include_once('inventory.php');
	$towers = getControlTowers();
        foreach ($towers as $tower) {
            foreach ($tower['fuel'] as $fuel) {
                if ($fuel['timeLeft']<48 && $fuel['purpose']==1) {
                    ?>
                    <div class="newmsg">
                        <img src="<?=getUrl()?>img/exc.gif" alt="Warning" />
                        Control tower '<?=$tower['towerName']?>' at <?=$tower['location']['moonName']?>
                        is low on <?=$fuel['fuelTypeName']?> (<?=$fuel['timeLeft']?> hours left)
                    </div>
                    <?php
                }
            }
        }
        
?>
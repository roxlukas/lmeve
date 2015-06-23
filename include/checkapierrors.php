<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.


 
 */

	checksession(); //check if we are called by a valid session
        $sql="SELECT *
            FROM `apistatus`
            WHERE date >= DATE_SUB( NOW( ) , INTERVAL 1 HOUR )
            AND errorCode >0";
	$errors = db_asocquery($sql);
        foreach ($errors as $error) {
            ?>
            <div class="newmsg">
                <img src="img/exc.gif" alt="Warning" />
                <a href="?id=8&id2=4"><?=$error['fileName']?>: <?=$error['errorMessage']?></a>
            </div>
            <?php
        }
        
?>

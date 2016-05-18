<?php
class widgets {
    public static function select($array,$id_key,$label_key,$undefined='- niezdefiniowany -',$undefined_id=-1) {
        $id=secureGETnum($id_key);
        ?><select name="<?=$id_key?>" onchange="this.form.submit();" /><option value="<?=$undefined_id?>" <?php if ($id=='') echo('selected'); ?> ><?=$undefined?></option><?php
            foreach($array as $row) {
                if ($row[$id_key]==$id) $selected='selected'; else $selected='';
                ?><option value="<?=$row[$id_key]?>" <?=$selected?>><?=$row[$label_key]?></option><?php
            }
        ?></select><?php
    }
}
?>
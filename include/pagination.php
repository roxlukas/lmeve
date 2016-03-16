<?php
function paginatorLimits() {
    return array(1 => 20, 2 => 50, 3 => 100);
}

function paginatorSQL() {
    $limits=paginatorLimits();
    $per_page_index=secureGETnum('per_page');
    $page=secureGETnum('page')>0 ? secureGETnum('page') : 0;
    if (array_key_exists($per_page_index, $limits)) $per_page=$limits[$per_page_index]; else $per_page=$limits[1];
    $page=$page * $per_page;
    $limit_sql="LIMIT $per_page OFFSET $page";
    return $limit_sql;
}

/**
 * Must be used WITHIN a <form> tag
 * 
 * @param type $url - form action url
 */
function showPaginator($url,$addFormTag=TRUE) {
    $limits=paginatorLimits();
    $per_page_index=secureGETnum('per_page');
    $page=secureGETnum('page')>0 ? secureGETnum('page') : 0;
    if (array_key_exists($per_page, $limits)) $per_page=$limits[$per_page_index]; else $per_page=$limits[1];
    
    if (preg_match('/\?/',$url)) $param_char='&'; else $param_char='?';
    ?>
    <?php if ($addFormTag) echo("<form action=\"$url\" method=\"POST\">"); ?>
    <table class="lmframework">
        <tr>
            <td><input type="button" value="&laquo;" onclick="document.getElementById('page').value--; this.form.submit();" <?php if($page<=0) echo('disabled'); ?>></button></td>
            <td><input type="text" id="page" name="page" size="3" value="<?=$page?>"></td>
            <td><input type="button" value="&raquo" onclick="document.getElementById('page').value++; this.form.submit();"></td>
            <td>on page: <select id="per_page" name="per_page" onchange="this.form.submit();">
                    <?php
                        foreach($limits as $key => $limit) {
                            if ($key==$per_page_index) $sel='selected'; else $sel='';
                            echo("<option value=\"$key\" $sel>$limit</option>");
                        }
                    ?>
                </select></td>
        </tr>
    </table>
    <?php if ($addFormTag) echo("</form>"); ?>
    <?php
}

?>
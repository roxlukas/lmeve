<?php

function parseWiki($text) {
    $id='11';
    $id2='0';
    $pattern[0]='/======(.*?)======/'; $replace[0]='<h1>$1</h1>';
    $pattern[1]='/=====(.*?)=====/'; $replace[1]='<h2>$1</h2>';
    $pattern[2]='/====(.*?)====/'; $replace[2]='<h3>$1</h3>';
    $pattern[3]='/===(.*?)===/'; $replace[3]='<h4>$1</h4>';
    $pattern[4]='/==(.*?)==/'; $replace[4]='<h5>$1</h5>';
    $pattern[5]='/=(.*?)=/'; $replace[5]='<h6>$1</h6>';
    $pattern[6]='/\*\*(.*?)\*\*/'; $replace[6]='<strong>$1</strong>';
    $pattern[7]='/__(.*?)__/'; $replace[7]='<u>$1</u>';
    $pattern[8]='/\s\/\/(.*?)\/\//'; $replace[8]=' <em>$1</em>';
    $pattern[9]='/^\/\/(.*?)\/\//'; $replace[9]='<em>$1</em>';
    $pattern[10]='/\-\-(.*?)\-\-/'; $replace[10]='<strike>$1</strike>';
    $pattern[11]='/  (.*?)\n/'; $replace[11]='<blockquote>$1</blockquote>';
    $pattern[12]='/\* (.*?)\n/'; $replace[12]='<ul><li>$1</li></ul>';
    $pattern[13]='/<\/ul><ul>/'; $replace[13]='';
    $pattern[14]='/# (.*?)\n/'; $replace[14]='<ol><li>$1</li></ol>';
    $pattern[15]='/<\/ol><ol>/'; $replace[15]='';
    //add formatting first
    //$text = nl2br($text);
    $text = preg_replace($pattern, $replace, $text);
    //replace links only after format has already been done
    unset($pattern); unset($replace);
    $pattern[0]='/\[Image-left:(.*?)\|(.*?)\]/'; $replace[0]='<img src="$1" alt="$2" title="$2" style="float: left;"/>';
    $pattern[1]='/\[Image-right:(.*?)\|(.*?)\]/'; $replace[1]='<img src="$1" alt="$2" title="$2" style="float: right;"/>';
    $pattern[2]='/\[Image-center:(.*?)\|(.*?)\]/'; $replace[2]='<center><img src="$1" alt="$2" title="$2" /></center>';
    $pattern[3]='/\[Image:(.*?)\|(.*?)\]/'; $replace[3]='<img src="$1" alt="$2" title="$2" />';
    $pattern[4]='/\[(http:\/\/.*?)\s(.*?)\]/'; $replace[4]='<a target="_blank" href="$1" title="$2">$2</a>';
    $pattern[5]='/\[(https:\/\/.*?)\s(.*?)\]/'; $replace[5]='<a target="_blank" href="$1" title="$2">$2</a>';
    $pattern[6]='/\[showinfo:(\d*?)\s(.*?)\]/'; $replace[6]='<input type="button" onclick="CCPEVE.showInfo($1)" value="$2">';
    $pattern[7]='/\[([\w\s]*?)\]/'; $replace[7]='<a href="?id='.$id.'&id2='.$id2.'&wikipage=$1" title="$1">$1</a>';
    $text = preg_replace($pattern, $replace, $text);
    unset($pattern); unset($replace);
    $pattern[0]='/[\n\r]+/'; $replace[0]='<br />'; //replace \(\n) with <br />
    $pattern[1]='/[>]<br \/>/'; $replace[1]='>'; //replace \(\n) with <br />
    $text = preg_replace($pattern, $replace, $text);
    //add wikiText div
    $text = '<div class="wikiText">'.$text.'</div>';
    return($text);
}

function getWikiPage($page) {
    $data=db_asocquery("SELECT * FROM `wiki` WHERE `wikipage`='$page';");
    if (count($data)>0) {
        return stripslashes_deep($data[0]);
    } else {
        return false;
    }
}

function showWikiPage($wikipage,$row) {
    if ($row===false) {
       echo("<h3>This page does not exist yet</h3>");
       if (checkrights("Administrator,EditWiki")) {
           ?>
           <form method="get" action="">You can 
           <input type="hidden" name="id" value="11" />
           <input type="hidden" name="id2" value="1" />
           <input type="hidden" name="wikipage" value="<?php echo($wikipage); ?>" />
           <input type="submit" value="Create" />
            it.</form>
           <?php
       }
    } else {
        echo(parseWiki($row['contents']));
    }
}
?>

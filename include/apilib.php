<?php


class XMLSerializer {

    // functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/

    public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}

function formatMaterials($materials) {
    $ret=array();
    if (count($materials>0)) {
        foreach ($materials as $row) {
            array_push($ret,array('typeID'=>$row['typeID'], 'typeName'=>$row['typeName'], 'quantity'=>$row['notperfect'], 'melevel'=>$row['waste'], 'consumed'=>$row['damagePerJob'] ));
        }
    }
    return $ret;
}

function json_beautify($json_string) {
    $ret='';
    $pos=0;
    $tabulator='    ';
    $eol=PHP_EOL;
    $prev='';
    $ignore=FALSE;
    for ($i=0; $i<=strlen($json_string); $i++) {
        $c=substr($json_string, $i, 1);
        if ($c=='"' && $prev != '\\') {
            $ignore=!$ignore;
        } else if(strpos('}]',$c)!==FALSE && !$ignore) {
            $ret.=$eol;
            $pos--;
            for ($j=0; $j<$pos; $j++) {
                $ret.=$tabulator;
            }
        }
        $ret.=$c;
        if (strpos(',{[',$c)!==FALSE && !$ignore) {
            $ret.=$eol;
            if ($c=='{' || $c=='[') $pos++;
            for ($j=0; $j<$pos; $j++) {
                $ret.=$tabulator;
            }
        }
        $prev=$c;
    }
    return $ret;
}

function encode($data, $nodes = 'data', $node = 'element') {
    $output = 'json';
    if(isset($_GET['output']) && $_GET['output'] == 'xml') $output = 'xml';
    switch($output) {
        case 'json':
            return json_beautify(json_encode($data, JSON_NUMERIC_CHECK ));
        case 'xml':
            return XMLSerializer::generateValidXmlFromArray($data,$nodes,$node);
        default:
            return json_beautify(json_encode($data, JSON_NUMERIC_CHECK ));
    }
}

function content_type() {
    $output = 'json';
    if(isset($_GET['output']) && $_GET['output'] == 'xml') $output = 'xml';
    switch($output) {
        case 'json':
            return 'application/json';
        case 'xml':
            return  'application/xml';
        default:
            return 'application/json';
    }
}

function checkApiKey($key) {
    $ret=db_asocquery("SELECT * FROM `lmnbapi` WHERE `apiKey`='$key';");
    if (count($ret)==1) {
        db_uquery("UPDATE `lmnbapi` SET `lastAccess`='".date('y-m-d H:i:s')."', `lastIP`='" . get_remote_addr() . "' WHERE `apiKey`='$key';");
        return TRUE;
    } else return FALSE;
}

function RESTfulError($msg,$http_error_code=400) {
    header("HTTP/1.1 $http_error_code $msg");
    header("Status: $http_error_code $msg");
     echo(encode(array('errorMsg' => $msg, 'errorCode' => $http_error_code)));
    die();
}

/**
 * deprecated
 * @param type $json
 */
function output($json) {
    echo(json_beautify($json));
}
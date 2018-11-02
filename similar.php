<?php

if(php_sapi_name()!="cli") {

if(!@$_POST['input']) {
    ?>
<form action="" method="POST">
от поставщика (&lt;Имя товара>): <br /><textarea name="input" cols="70" rows="15"></textarea><br />
спарсенные имена (&lt;Имя товара>&lt;TAB>&lt;Ссылка на товар донора>): <br /><textarea name="parsed" cols="70" rows="15"></textarea><br />
<input type=submit value="send">
</form>
    <?
    exit;
}

$input = $_POST['input'];

$parsed = $_POST['parsed'];

} else {
    $input = file_get_contents($argv[1]);
    $parsed = file_get_contents($argv[2]);
}

l("<pre>");

$input = array_map(function($v){return [trim($v)];}, explode("\n", $input));
$parsed = array_map(function($v){
        $v = explode("\t", trim($v));
        if(strlen($v[0])==0) return [];
        return [-1 => $v[1], 0 => $v[0]];
    }, explode("\n", $parsed));
$parsedcut = [];
foreach($parsed as $k => $v)
    if(count($v)!=0)
        $parsedcut[] = $v;
$parsed = $parsedcut;

$busy = [];
foreach($parsed as $pkey => &$parsed_item) {
    list($maxSimIndex, $ikey) = findSimilar($input, $parsed_item);
    // l('$pkey', $pkey);
    if(isset($busy[$ikey])) {
        $pkey_old = $busy[$ikey];
        // l('$input[$ikey][0]', $input[$ikey][0], '$parsed_item[0]', $parsed_item[0], '$maxSimIndex', $maxSimIndex, '$pkey_old', $pkey_old, '$parsed[$pkey_old][2]', $parsed[$pkey_old][2]);
        $excludes = [];
        $iter = 1;
        $sim_parsed_old = $parsed[$pkey_old][2];
        while($maxSimIndex < $sim_parsed_old[count($sim_parsed_old)-1] && count($input)!=count($excludes)) {
            $parsed_item[1] = 9999999;
            $parsed_item[2][] = $maxSimIndex . "**$ikey";
            $excludes[$ikey] = cut($input[$ikey][0]);
            list($maxSimIndex, $ikey) = findSimilar($input, $parsed_item, $excludes);
            if(!isset($busy[$ikey])) break;
        }
        if(isset($busy[$ikey])) {
            $pkey_old = $busy[$ikey];
            $parsed[$pkey_old][1] = 9999999;
            $parsed[$pkey_old][2][] = "$maxSimIndex//" . $parsed[$pkey_old][1];
            
            while(isset($busy[$ikey])) {
                list($maxSimIndex, $ikey) = findSimilar($input, $parsed[$pkey_old], $excludes);
                
            }
            // do {
            //     $pkey_old = $busy[$ikey];
            //     $busy[$ikey] = $pkey;
            //     $excludes[$ikey] = cut($input[$ikey][0]);
            // } while(($maxSimIndex < $sim_parsed_old[count($sim_parsed_old)-1] || isset($busy[$ikey])) && count($input)!=count($excludes))
        }
    }
    $parsed_item[1] = $ikey;
    $parsed_item[2][] = $maxSimIndex."__$ikey";
    $busy[$ikey] = $pkey;
}

$parsedKeys = array_map(function($v) {return $v[1];}, $parsed);
for($i=0; $i<count($input); $i++) {
    $search = array_search($i, $parsedKeys);
    if($search!==false) continue;
    $parsed[] = ["", $i];
}
// var_dump($parsed);exit;
$sortarr = array_map(function($v) {return $v[1];}, $parsed);
array_multisort($sortarr, $parsed);

foreach($parsed as $key => $val)
l($val[1]."\t". @implode(", ", $val[2]) . "\t" . $val[0] . "\t" . $val[-1]);


function shiftToOtherInput() {

}

function shiftToEnd() {

}



$simarr = [];

function findSimilar($input, $parsed_item, $excludes = []) {
    $maxSimIndex = 0;
    $ikey = false;
    $parsed_item_cut = cut($parsed_item[0]);
    foreach($input as $key => $input_item)
    {
        $input_item_cut = cut($input_item[0]);
        if(in_array(cut($input_item_cut), $excludes)) continue;
        if(!isset($simarr[$input_item_cut . '_' . $key . '_' . $parsed_item_cut])) {
            similar_text(cut($input_item_cut), cut($parsed_item_cut), $sim);
            $simarr[$input_item_cut . '_' . $key . '_' . $parsed_item_cut] = $sim;
        } else $sim = $simarr[$input_item_cut . '_' . $key . '_' . $parsed_item_cut];

        if($sim > $maxSimIndex) {
            $maxSimIndex = $sim;
            $ikey = $key;
        }
    }
    return [$maxSimIndex, $ikey];
}

function cut($str) {
    //return $str;
    return strtolower(preg_replace('@ ?\([^\)]{30,}\)|[^A-z \d]@', '', $str));
}

function l() {
    $str = [];
    for($i=0; $i<func_num_args(); $i++)
        $str[] = func_get_arg($i);
    echo implode(", ", $str)."\n";
}
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
    if(isset($busy[$ikey])) {
        $pkey_old = $busy[$ikey];
        l('$input[$ikey][0]', $input[$ikey][0], '$parsed_item[0]', $parsed_item[0], '$maxSimIndex', $maxSimIndex, '$pkey_old', $pkey_old, '$parsed[$pkey_old][2]', $parsed[$pkey_old][2]);
        $excludes = [];
        $iter = 1;
        $parsed_old = $parsed[$pkey_old][2];
        while($maxSimIndex < $parsed_old[count($parsed_old)-1] && count($input)!=count($excludes)) {
            // if($iter==1) {
            //     echo $parsed_item[0] . " --- " . "\n";
            //     echo $input[$busy[$ikey]][0]  . " --- " . $busy[$ikey] . "\n";
            // }
            // echo ($iter++)." ".$maxSimIndex . "**$ikey"."\n";
        	$parsed_item[1] = 9999999;
        	$parsed_item[2][] = $maxSimIndex . "**$ikey";
        	$excludes[] = $input[$ikey][0];
        	list($maxSimIndex, $ikey) = findSimilar($input, $parsed_item, $excludes);
 			if(!isset($busy[$ikey])) break;
            $pkey_old = $busy[$ikey];
        }
        if(isset($busy[$ikey])) {
            $parsed[$pkey_old][2][] = "//" . $parsed[$pkey_old][1];
            $parsed[$pkey_old][1] = 9999999;
            $busy[$ikey] = $pkey;
        }
    }
    $parsed_item[1] = $ikey;
    $parsed_item[2][] = $maxSimIndex;
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




function findSimilar($input, $parsed_item, $excludes = []) {
    $maxSimIndex = 0;
    $ikey = false;
    foreach($input as $key => $input_item) {
    	if(in_array($input_item[0], $excludes)) continue;
        similar_text($input_item[0], $parsed_item[0], $sim);
        if($sim > $maxSimIndex) {
            $maxSimIndex = $sim;
            $ikey = $key;
        }
    }
    return [$maxSimIndex, $ikey];
}

function l() {
    $str = [];
    for($i=0; $i<func_num_args(); $i++)
        $str[] = func_get_arg($i);
    echo implode(", ", $str)."\n";
}
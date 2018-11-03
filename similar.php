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
        return [
            'name' => $v[0],
            'url' => $v[1]
        ];
    }, explode("\n", $parsed));

$sim = new SimilarProductNames($input, $parsed);

while(true) {
    $r = [];
    $r[] = $sim->firstIterator();
    $r[] = $sim->secondIterator();
    $r[] = $sim->thirdIterator();
    $r[] = $sim->secondIterator();
    foreach($r as $v)
        if($v) continue 2;
    break;
}

$result = $sim->getResult();

// ищем несвязавшиеся имена и связываем их с пустышкой
$parsedKeys = array_map(function($v) {return $v['assign'];}, $result);
for($i=0; $i<count($input); $i++) {
    $search = array_search($i, $parsedKeys);
    if($search!==false) continue;
    $result[] = ['assign' => $i];
}

// сортируем результат по порядковому номеру строки из $input
$sortarr = array_map(function($v) {return $v['assign'];}, $result);
array_multisort($sortarr, $result);

foreach($result as $r)
l(
    $r['assign'] . "\t".
    implode(", ", array_merge((isset($r['sim']) ? [-1 => $r['sim']] : []), $r['history'])) . "\t" .
    @$r['name'] . "\t" .
    $r['url']
);


////////// LOGout
function l() {
    $str = [];
    for($i=0; $i<func_num_args(); $i++)
        $str[] = func_get_arg($i);
    echo implode(", ", $str)."\n";
}
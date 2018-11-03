<?php
$n1 = "Набор OneTwoSet - Hopper цвет 5018 magenta deer (3830116 OneTwo; 80261 Hopper; 3890215 Chest Wallet; 3890416 Pencil Pouch; 2890315 Pencil box)";
$n2 = "Школьный набор Deuter OneTwoSet - Hopper magenta deer";
$n3 = "Сумка на пояс Neo Belt II цвет 4505 silver-magenta";

function pre($str, $regexp='@[^A-z \d]@') {
    return strtolower(preg_replace('@ ?\([^\)]*\)@', '', $str));
    strtolower(preg_replace($regexp, '', $str));
}

similar_text(pre($n1), pre($n2), $sim);
echo $sim."\n";
//echo " // " . pre($n1) . " // " . pre($n2) ."\n";

similar_text(pre($n3), pre($n2), $sim);
echo $sim."\n";exit;
//echo " // " . pre($n2) . " // " . pre($n3) ."\n";

similar_text($n2, $n1, $sim);
echo $sim."\n";

similar_text($n3, $n1, $sim);
echo $sim."\n";

function aa() {
    return [1, 22];
}

list($b, $jj) = aa();
var_dump($b, $jj);
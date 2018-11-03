<?php

class SimilarProductNames {
    private $input = [];
    private $a1 = $a2 = $a3 = [];

    constructor($input, $parsed) {
        $this->input = $input;
        $this->a1 = $parsed;
    }

    function firstIterator() {

    }

    // ищем задвоенные записи (там где по 2 претендента на 1 место) и выселяем слабейшего в массив $this->a3
    function secondIterator() {
        $result = false;
        foreach($this->a2 as &$el) {
            if(count($el)==1) continue;
            $result = true;
            if($el[0]['sim'] > $el[1]['sim'])
                $i1 = 1; $i2 = 0;
            else
                $i1 = 0; $i2 = 1;
            $el[$i1]['history'][] = $el[$i1]['sim'] . '__' $el[$i1]['assign'];
            $el[$i1]['excludes'][] = $el[$i1]['assign'];
            unset($el[$i1]['sim']);
            unset($el[$i1]['assign']);
            $this->a3[] = $el[$i2];
            array_splice($el, $i2, 1);
        }
        return $result;
    }

    //
    function thirdIterator() {
        foreach($this->a3 as $k => $el) {
            list($sim, $ikey) = $this->findSim($this->input, $el);
            if($ikey===false) {
                $result = true;
                $this->a4[] = $el;
                unset($this->$a3[$k])
                return true;
            }
            $this->a2[$ikey][] = $el;
            unset($this->$a3[$k])
            return true;
        }
        return false;
    }


    private function findSim($input, $parsed_item) {
        $maxSimIndex = 0;
        $ikey = false;
        $parsed_item_cut = $this->cut($parsed_item[0]);
        foreach($input as $key => $input_item)
        {
            $input_item_cut = $this->cut($input_item[0]);
            if(in_array($key, $parsed_item['excludes'])) continue;
            similar_text($this->cut($input_item_cut), $this->cut($parsed_item_cut), $sim);
            
            if($sim > $maxSimIndex) {
                $maxSimIndex = $sim;
                $ikey = $key;
            }
        }
        return [$maxSimIndex, $ikey];
    }

    private function cut($str) {
        //return $str;
        return strtolower(preg_replace('@ ?\([^\)]{30,}\)|[^A-z \d]@', '', $str));
    }
}

?>
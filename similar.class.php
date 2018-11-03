<?php

class SimilarProductNames {
    private $input = [], $a1 = [], $a2 = [], $a3 = [], $a4 = [];

    function __construct($input, $parsed) {
        $this->input = $input;
        $this->a1 = $parsed;
    }

    function firstIterator() {
        foreach($this->a1 as $k => &$el) {
            list($sim, $ikey) = $this->findSim($this->input, $el);
            if($ikey===false) {
                $result = true;
                $el['assign'] = 999999;
                $this->a4[] = $el;
                unset($this->a1[$k]);
                return true;
            }
            $el['assign'] = $ikey;
            $el['sim'] = $sim;
            $this->a2[$ikey][] = $el;
            unset($this->a1[$k]);
            return true;
        }
        return false;
    }

    // ищем задвоенные записи (там где по 2 претендента на 1 место) и выселяем слабейшего в массив $this->a3
    function secondIterator() {
        $result = false;
        foreach($this->a2 as $k => &$el) {
            if(count($el)==1) continue;
            $result = true;
            if($el[0]['sim'] > $el[1]['sim'])
                $i1 = 1;
            else
                $i1 = 0;
            $el[$i1]['history'][] = $el[$i1]['sim'] . '__' . $el[$i1]['assign'];
            $el[$i1]['excludes'][] = $el[$i1]['assign'];
            unset($el[$i1]['assign']);
            unset($el[$i1]['sim']);
            $this->a3[$k] = $el[$i1];
            array_splice($el, $i1, 1);
        }
        return $result;
    }

    //
    function thirdIterator() {
        if(php_sapi_name()=='cli')
        l('count(a1)', count($this->a1), 'count(a2)', count($this->a2), 'count(a3)', count($this->a3), 'count(a4)', count($this->a4));
        foreach($this->a3 as $k => $el) {
            list($sim, $ikey) = $this->findSim($this->input, $el);
            if($ikey===false) {
                $result = true;
                $el['assign'] = 999999;
                $this->a4[] = $el;
                unset($this->a3[$k]);
                return true;
            }
            $el['assign'] = $ikey;
            $el['sim'] = $sim;
            $this->a2[$ikey][] = $el;
            unset($this->a3[$k]);
            return true;
        }
        return false;
    }


    function getResult() {
        $from_a2 = array_map(function($el) {
            return $el[0];
        }, $this->a2);
        $result = [];
        foreach($from_a2 as $v)
            $result[] = $v;
        foreach($this->a4 as $v)
            $result[] = $v;
        return $result;
    }

    private function findSim($input, $parsed_item) {
        $maxSimIndex = 0;
        $ikey = false;
        $parsed_item_cut = $this->cut($parsed_item['name']);
        foreach($input as $key => $input_item)
        {
            $input_item_cut = $this->cut($input_item[0]);
            if(isset($parsed_item['excludes']) && in_array($key, $parsed_item['excludes'])) continue;
            similar_text($input_item_cut, $parsed_item_cut, $sim);
            
            if($sim > $maxSimIndex) {
                $maxSimIndex = $sim;
                $ikey = $key;
            }
        }
        return [$maxSimIndex, $ikey];
    }

    private function cut($str) {
        //return $str;
        return strtolower(preg_replace('@ ?\([^\)]{30,}\)@', '', $str));
        return strtolower(preg_replace('@ ?\([^\)]{30,}\)|[^A-z \d]@', '', $str));
    }
}

?>
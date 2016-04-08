<?php 

namespace Sirian\YMLParser\Helper;

class Str
{

    public static function between($string = '', $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        $result = substr($string, $ini, $len);
        return $result;
    }
    
}

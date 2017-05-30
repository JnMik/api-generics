<?php
/**
 * Created by PhpStorm.
 * User: jm
 * Date: 04/04/16
 * Time: 7:13 PM
 */

namespace Support3w\Api\Generic\Normalizer\String;


use Support3w\Api\Generic\Normalizer\Normalizable;

class HyphenFolderName implements Normalizable {

    public function normalize($string)
    {
        $string = trim($string);

        // Replace all weird characters with dashes
        $string = preg_replace('/[^\w\-]+/u', '-', $string);

        // Only allow one dash separator at a time (and make string lowercase)
        $output = mb_strtolower(preg_replace('/--+/u', '-', $string), 'UTF-8');

        return trim($output);
    }
}
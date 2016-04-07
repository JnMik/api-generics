<?php

namespace Support3w\Api\Generic\Utils;

class StringTool
{

    function sanitize($string = '', $is_filename = FALSE, $spacing_character = '-')
    {

        $string = trim($string);

        // Replace all weird characters with dashes
        $string = preg_replace('/[^\w\-' . ($is_filename ? '~_\.' : '') . ']+/u', $spacing_character, $string);

        // Only allow one dash separator at a time (and make string lowercase)
        $output = mb_strtolower(preg_replace('/--+/u', $spacing_character, $string), 'UTF-8');

        return $output;
    }

}
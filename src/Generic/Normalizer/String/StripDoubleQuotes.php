<?php

namespace Support3w\Api\Generic\Normalizer\String;

use Support3w\Api\Generic\Normalizer\Normalizable;

class StripDoubleQuotes implements Normalizable
{
    public function normalize($string)
    {
        return str_replace('"', '', $string);
    }
} 
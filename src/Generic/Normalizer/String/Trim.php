<?php

namespace Support3w\Api\Generic\Normalizer\String;

use Support3w\Api\Generic\Normalizer\Normalizable;

class Trim implements Normalizable
{
    public function normalize($string)
    {
        return trim($string);
    }
} 
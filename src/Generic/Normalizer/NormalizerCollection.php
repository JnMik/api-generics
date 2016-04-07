<?php

namespace Support3w\Api\Generic\Normalizer;

class NormalizerCollection implements Normalizable
{

    protected $normalizers = array();

    public function __construct(array $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    public function add(Normalizable $normalizer)
    {
        $this->normalizers[] = $normalizer;
    }

    public function normalize($object)
    {
        /**
         * @var Normalizable $normalizer
         */
        foreach ($this->normalizers as $normalizer) {
            $object = $normalizer->normalize($object);
        }
        return $object;
    }
} 
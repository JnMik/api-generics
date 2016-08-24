<?php

namespace Support3w\Api\Generic\Controller;

use Crak\Component\RestNormalizer\Builder\ResponseBuilder;

/**
 * Class Controller
 */
abstract class Controller
{
    /**
     * @var \Closure
     */
    protected $responseBuilderClosure;

    /**
     * @param \Closure $responseBuilderClosure
     */
    public function __construct(\Closure $responseBuilderClosure)
    {
        $this->responseBuilderClosure = $responseBuilderClosure;
    }

    /**
     * @param mixed|null $object
     *
     * @return ResponseBuilder
     */
    public function invokeResponseBuilder($object = null)
    {
        return $this->responseBuilderClosure->__invoke($object);
    }
}
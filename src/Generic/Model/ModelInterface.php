<?php

namespace Support3w\Api\Generic\Model;

/**
 * Interface ModelInterface
 */
interface ModelInterface
{
    /**
     * @return array
     */
    public function jsonSerialize();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * 
     * @return DefaultModel
     */
    public function setId($id);
}
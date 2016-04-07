<?php

namespace Support3w\Api\Generic\Model;

/**
 * Class DefaultModel
 *
 * @package  Model
 */
abstract class DefaultModel implements \JsonSerializable
{
    const LONG_NAME = __CLASS__;

    /**
     * @var integer
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     *
     * @return DefaultModel
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setId($id)
    {

        if ($id == $this->id) {
            return $this;
        }

        if ($this->id !== null) {
            throw new \BadMethodCallException("The ID for this model has been set already");
        }

        try {

            $id = (int)$id;

            if ($id < 1) {
                throw new \InvalidArgumentException("ID is invalid");
            }

        } catch (\Exception $e) {
            throw new \InvalidArgumentException("ID is invalid");
        }

        $this->id = $id;

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    abstract public function jsonSerialize();

    abstract public function loadFromArray($array);

    /**
     * @param $json
     */
    public function loadFromJson($json)
    {
        $array = json_decode($json, true);
        $this->loadFromArray($array);
    }
}

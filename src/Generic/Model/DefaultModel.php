<?php

namespace Support3w\Api\Generic\Model;

use Stringy\Stringy as S;

/**
 * Class DefaultModel
 *
 * @package Model
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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by json_encode,
     * which is a value of any type other than a resource.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $response = array();
        foreach (get_object_vars($this) as $property => $value) {
            $underscored = S::create($property)->underscored()->__toString();
            $response[$underscored] = $this->{$property};
        }

        $response['id'] = $this->getId();

        return $response;
    }

    /**
     * @param array $array
     */
    public function loadFromArray(array $array)
    {
        if (isset($array['id'])) {
            $this->setId($array['id']);
        }
        foreach (get_object_vars($this) as $property => $value) {
            $underscored = S::create($property)->underscored()->__toString();
            if (isset($array[$underscored])) {
                $this->{$property} = $array[$underscored];
            }
        }
    }

    /**
     * @param $json
     */
    public function loadFromJson($json)
    {
        $array = json_decode($json, true);
        $this->loadFromArray($array);
    }
}

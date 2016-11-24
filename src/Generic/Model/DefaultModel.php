<?php

namespace Support3w\Api\Generic\Model;

use Stringy\Stringy as S;
use Support3w\Api\Generic\Exception\InvalidJsonException;

/**
 * Class DefaultModel
 *
 * @package Model
 */
abstract class DefaultModel implements \JsonSerializable, ModelInterface
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

            if(is_object($this->{$property}) && is_a($this->{$property}, 'datetime')) {
                $response[$underscored] = $this->{$property}->format('Y-m-d H:i:s');
            }else{
                $response[$underscored] = $this->{$property};
            }
        }

        $response['id'] = $this->getId();

        return $response;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function loadFromArray(array $array)
    {
        if (isset($array['id'])) {
            $this->setId($array['id']);
        }
        foreach (get_object_vars($this) as $property => $value) {
            $underscored = S::create($property)->underscored()->__toString();
            if (isset($array[$underscored])) {
                if(is_object($this->{$property}) && is_a($this->{$property}, 'datetime')) {
                    $this->{$property} = new \Datetime($array[$underscored]);
                }else{
                    $this->{$property} = $array[$underscored];
                }
            }
        }
        return $this;
    }

    /**
     * @param $json
     *
     * @return $this
     */
    public function loadFromJson($json)
    {
        $array = json_decode($json, true);

        if(!is_array($array)) {
            throw new InvalidJsonException('Result cannot be decoded to array, is JSON valid ? ');
        }
        $this->loadFromArray($array);
        return $this;
    }
}

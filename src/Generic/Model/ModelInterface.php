<?php

namespace Support3w\Api\Generic\Model;


interface ModelInterface
{

    public function jsonSerialize();

    public function getId();

    public function setId($id);
}
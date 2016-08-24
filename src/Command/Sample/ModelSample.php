<?php

namespace Support3w\Api\Command\Sample;

use Support3w\Api\Generic\Model\DefaultModel;
use Support3w\Api\Generic\Model\ModelInterface;

/**
 * Class ModelSample
 *
 * @package //PACKAGE_NAME_PLACE_HOLDER
 */
class ModelSample extends DefaultModel implements ModelInterface
{

    const LONG_NAME = __CLASS__;

    /**
     * @var integer
     */
    public $id;

    //PROPERTIES_PLACE_HOLDER

    /**
     * @var bool
     */
    public $deleted;

    public function __construct()
    {
        $this->deleted = 0;
        //DEFAULT_PROPERTIES_VALUES_PLACE_HOLDER
    }

} 
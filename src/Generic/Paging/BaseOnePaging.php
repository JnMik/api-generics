<?php

namespace Support3w\Api\Generic\Paging;

use Support3w\Api\Generic\Exception\InvalidPagingParameterException;

class BaseOnePaging extends PagingAbstract
{

    const DEFAULT_START_PARAMETER = 1;

    protected function getDefaultStartParameter()
    {
        return self::DEFAULT_START_PARAMETER;
    }

    /**
     * @param $start
     *
     * @throws \Support3w\Api\Generic\Exception\InvalidPagingParameterException
     */
    protected function guardAgainstInvalidStartParameter($start)
    {

        if (!is_numeric($start)) {
            throw new InvalidPagingParameterException("Invalid value for start parameter");
        }

        if ($start == 0) {
            throw new InvalidPagingParameterException("Start parameter value cannot be zero on a base one paging.");
        }
    }


    public function getPageCount()
    {
        return ceil($this->rowsCount / $this->limit);
    }


    /**
     * @return bool|int
     */
    public function getNextStartValue()
    {

        if ($this->getStart() >= $this->rowsCount) {
            return false;
        }

        if ($this->getStart() + $this->limit >= $this->rowsCount) {
            return false;
        }

        $nextLinkStartValue = $this->getStart() + $this->limit;

        return $nextLinkStartValue;

    }
}
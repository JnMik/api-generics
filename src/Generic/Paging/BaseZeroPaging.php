<?php

namespace Support3w\Api\Generic\Paging;

use Support3w\Api\Generic\Exception\InvalidPagingParameterException;

class BaseZeroPaging extends PagingAbstract
{

    const DEFAULT_START_PARAMETER = 0;

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

    }

    public function getPageCount()
    {
        return ceil(($this->rowsCount + 1) / $this->limit);
    }

    /**
     * @return bool|int
     */
    public function getNextStartValue()
    {

        if ($this->getStart() >= $this->rowsCount) {
            return false;
        }


        if ($this->getStart() + $this->limit > $this->rowsCount) {
            return false;
        }


        $nextLinkStartValue = $this->getStart() + $this->limit;

        return $nextLinkStartValue;
    }
}
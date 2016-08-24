<?php

namespace Support3w\Api\Generic\Paging;

class PaginatorService
{

    /**
     * @var PagingAbstract
     */
    private $paging;

    public function __construct(PagingAbstract $paging)
    {

        $this->paging = $paging;
    }

    public function getBaseOnePaging()
    {
        return $this->paging;
    }

    public function getBaseZeroPaging()
    {
        if ($this->paging instanceof BaseOnePaging) {
            $baseOneToBaseZeroTransformer = new BaseOneToBaseZeroTransformer($this->paging);
            return $baseOneToBaseZeroTransformer->transform();
        }
        return $this->paging;
    }

    public function setRowsCount($rowsCount)
    {
        $this->paging->setRowsCount($rowsCount);
    }
}
<?php

namespace Support3w\Api\Generic\Paging;

class BaseOneToBaseZeroTransformer
{

    private $baseOnePaging;

    public function __construct(BaseOnePaging $paging)
    {
        $this->baseOnePaging = $paging;
    }

    public function transform()
    {

        $baseZeroPaging = new BaseZeroPaging(
            $this->baseOnePaging->getStart(),
            $this->baseOnePaging->getLimit(),
            $this->baseOnePaging->getPageCount(),
            $this->baseOnePaging->getMaxLimitAllowed()
        );

        return $baseZeroPaging;

    }

} 
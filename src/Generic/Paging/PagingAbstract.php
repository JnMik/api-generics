<?php

namespace Support3w\Api\Generic\Paging;

use Support3w\Api\Generic\Exception\InvalidPagingParameterException;

abstract class PagingAbstract
{

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $rowsCount;

    /**
     * @var int
     */
    protected $maxLimitAllowed;


    abstract function getPageCount();

    abstract protected function getDefaultStartParameter();

    abstract protected function guardAgainstInvalidStartParameter($start);

    abstract function getNextStartValue();

    public function __construct($start, $limit = 100, $rowsCount = null, $maxLimitAllowed)
    {

        if (is_null($start)) {
            $start = $this->getDefaultStartParameter($start);
        }

        if (is_null($limit)) {
            $limit = 100;
        }

        $this->guardAgainstInvalidStartParameter($start);

        if (!is_numeric($limit)) {
            throw new InvalidPagingParameterException("Invalid value for limit parameter");
        }

        $this->start = $start;
        $this->limit = $limit;
        $this->rowsCount = $rowsCount;
        $this->maxLimitAllowed = $maxLimitAllowed;

        $this->guardAgainstLimitExceedingAllowedValue();

    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $rowsCount
     * @throws \Support3w\Api\Generic\Exception\InvalidPagingParameterException
     */
    public function setRowsCount($rowsCount)
    {
        if (!is_numeric($rowsCount)) {
            throw new InvalidPagingParameterException("Invalid value for rows count parameter");
        }

        $this->rowsCount = $rowsCount;
    }

    /**
     * @return mixed
     */
    public function getRowsCount()
    {
        return $this->rowsCount;
    }

    /**
     * @param mixed $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $maxLimitAllowed
     */
    public function setMaxLimitAllowed($maxLimitAllowed)
    {
        $this->maxLimitAllowed = $maxLimitAllowed;
    }

    /**
     * @return int
     */
    public function getMaxLimitAllowed()
    {
        return $this->maxLimitAllowed;
    }

    public function getResponse()
    {

        $previousLinkStartValue = $this->getPreviousStartValue();
        $nextLinkStartValue = $this->getNextStartValue();

        $response = array();

        $response['first'] = null;
        $response['previous'] = null;
        $response['next'] = null;
        $response['last'] = null;

        if ($previousLinkStartValue || $previousLinkStartValue == $this->getDefaultStartParameter()) {
            $response['first'] = $this->buildLink($this->getDefaultStartParameter(), $this->limit);
        }

        if ($previousLinkStartValue || ($previousLinkStartValue == $this->getDefaultStartParameter() && $this->getStart() != $this->getDefaultStartParameter())) {
            $response['previous'] = $this->buildLink($previousLinkStartValue, $this->limit);
        }


        if ($nextLinkStartValue) {
            $response['next'] = $this->buildLink($nextLinkStartValue, $this->limit);
            $nbrPage = floor($this->getRowsCount() / $this->limit);
            $response['last'] = $this->buildLink(($nbrPage * $this->limit), $this->limit);
        }

        return $response;

    }

    private function buildLink($start, $limit)
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = parse_url($url);

        $parameters = $_GET;
        if (isset($parameters['start'])) {
            unset($parameters['start']);
        }
        if (isset($parameters['limit'])) {
            unset($parameters['limit']);
        }
        $parameters = http_build_query($parameters);
        $parameters = urldecode($parameters);
        $parameters = str_replace('[0]', '[]', $parameters);
        return $url['scheme'] . '://' . $url['host'] . ($url['port'] ? ':' . $url['port'] : '') . $url['path'] . '?start=' . $start . '&limit=' . $limit . '&' . $parameters;
    }

    /**
     * @return bool|int
     */
    public function getPreviousStartValue()
    {

        if ($this->start == $this->getDefaultStartParameter()) {
            return false;
        }

        $previousLinkStartValue = $this->start - $this->limit;

        if ($previousLinkStartValue < $this->getDefaultStartParameter()) {
            $previousLinkStartValue = $this->getDefaultStartParameter();
        }

        return $previousLinkStartValue;

    }

    public function guardAgainstLimitExceedingAllowedValue()
    {
        if ($this->limit > $this->maxLimitAllowed) {
            throw new InvalidPagingParameterException('Limit value given in the request exceed the limit allowed by the API.');
        }
    }

}
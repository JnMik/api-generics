<?php

namespace Support3w\Api\Generic\Repository;

use Doctrine\DBAL\Driver\Connection;
use Support3w\Api\Generic\Exception\DataCreationException;
use Support3w\Api\Generic\Exception\DataModificationException;
use Support3w\Api\Generic\Exception\InvalidDataIdException;
use Support3w\Api\Generic\Model\ModelInterface;
use Support3w\Api\Generic\Paging\PaginatorService;

/**
 * Class RepositoryBase
 */
abstract class RepositoryBase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $mainTableAlias;

    /**
     * @var array
     */
    protected $fieldTableAlias;

    /**
     * @var array
     */
    protected $equalFields;

    /**
     * @var array
     */
    protected $notEqualFields;

    /**
     * @var array
     */
    protected $joinEqualFields;

    /**
     * @var array
     */
    protected $joinNotEqualFields;

    /**
     * @var array
     */
    protected $inFields;

    /**
     * @var array
     */
    protected $notInFields;

    /**
     * @var array
     */
    protected $joinInFields;

    /**
     * @var array
     */
    protected $joinNotInFields;


    /**
     * @param Connection $connection
     * @param string $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;

        $this->mainTableAlias = '';
        $this->fieldTableAlias = array();
        $this->equalFields = array();
        $this->notEqualFields = array();
        $this->joinEqualFields = array();
        $this->joinNotEqualFields = array();
        $this->inFields = array();
        $this->notInFields = array();
        $this->joinInFields = array();
        $this->joinNotInFields = array();
    }

    /**
     * @param $params
     * @return int
     */
    public function count($params = null)
    {
        $where = $this->prepareWhereClauseFromQueryString($params);

        $data = $this->connection->fetchAssoc('SELECT
                 count(*) rowsCount
                 FROM `' . $this->table . '` ' . $this->mainTableAlias . '
                 WHERE 1=1 ' . $where, array_values($params));

        return $data['rowsCount'];
    }

    /**
     * @param PaginatorService $paginatorService
     *
     * @return array
     */
    public function fetchAll(PaginatorService $paginatorService)
    {
        $paginatorService->setRowsCount($this->count());
        $paging = $paginatorService->getBaseZeroPaging();
        $data = $this->connection->fetchAll('SELECT * FROM `' . $this->table . '` ' . $this->mainTableAlias . ' WHERE deleted = 0 LIMIT ' . $paging->getStart() . "," . $paging->getLimit());

        return $data;
    }

    /**
     * @param integer $id
     *
     * @return ModelInterface|null
     */
    public function findById($id)
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM `' . $this->table . '` ' . $this->mainTableAlias . ' WHERE id = ?', array((int) $id));

        return $data;
    }

    /**
     * @param PaginatorService $paginatorService
     * @param array $params
     *
     * @return array
     */
    public function findByParameters(PaginatorService $paginatorService, $params)
    {
        $paginatorService->setRowsCount($this->count($params));
        $paging = $paginatorService->getBaseZeroPaging();
        $fields = $this->prepareFieldsFromQueryString($params);
        $where = $this->prepareWhereClauseFromQueryString($params);
        $data = $this->connection->fetchAll('SELECT ' . $fields . ' FROM `' . $this->table . '` ' . $this->mainTableAlias . ' WHERE 1=1 ' . $where . ' LIMIT ' . $paging->getStart() . "," . $paging->getLimit(), array_values($params));

        return $data;
    }

    /**
     * @param array $joinEqualFields
     *
     * @return mixed
     */
    abstract public function buildJoinEqualFields(array $joinEqualFields);

    /**
     * @param array $joinNotEqualFields
     *
     * @return mixed
     */
    abstract public function buildJoinNotEqualFields(array $joinNotEqualFields);

    /**
     * @param array $joinInFields
     *
     * @return mixed
     */
    abstract public function buildJoinInFields(array $joinInFields);

    /**
     * @param array $joinNotInFields
     *
     * @return mixed
     */
    abstract public function buildJoinNotInFields(array $joinNotInFields);

    /**
     * @param ModelInterface $model
     *
     * @return ModelInterface
     * @throws \Support3w\Api\Generic\Exception\DataCreationException
     */
    public function create(ModelInterface $model)
    {
        $success = $this->connection->insert('`' . $this->table . '`', $this->escapeFieldName($model->jsonSerialize()));

        if (!$success) {
            throw new DataCreationException('Query failed');
        }

        $lastInsertedId = (int) $this->connection->lastInsertId();
        $model->setId($lastInsertedId);

        return $model;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function escapeFieldName($array)
    {
        foreach ($array as $key => $value) {
            $array['`' . $key . '`'] = $value;
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * @param ModelInterface $model
     * @param int $id
     *
     * @return ModelInterface
     * @throws \Support3w\Api\Generic\Exception\InvalidDataIdException
     * @throws \Support3w\Api\Generic\Exception\DataModificationException
     */
    public function update(ModelInterface $model, $id)
    {
        if (is_null($model->getId())) {
            throw new InvalidDataIdException('ID must be given in Json too');
        }

        try {
            $this->connection->update($this->table, $this->escapeFieldName($model->jsonSerialize()), array(
                'id' => $id,
            ));
        }
        catch (\Exception $e) {
            throw new DataModificationException('Query failed, that ID may not exist or there is nothing to update. ' . $e->getMessage());
        }

        return $model;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Support3w\Api\Generic\Exception\DataModificationException
     */
    public function delete($id)
    {
        try {
            $this->connection->update(
                $this->table,
                array('deleted' => 1),
                array('id' => $id)
            );

        } catch (\Exception $e) {
            throw new DataModificationException('Query failed, that ID may not exist or the object is already deleted.');
        }

        return true;
    }

    /**
     * @param array $fieldNames
     *
     * @return array
     */
    public function resolveTableAliasForParamsArray(array $fieldNames)
    {
        $fieldNamesWithTableAliases = array();

        foreach ($fieldNames as $field) {
            $fieldNamesWithTableAliases[] = $this->getTableAliasForParam($field) . '.' . $field;
        }

        return $fieldNamesWithTableAliases;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function resolveTableAliasForParam($field)
    {
        return $this->getTableAliasForParam($field) . '.' . $field;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getTableAliasForParam($field)
    {
        if (!isset($this->fieldTableAlias[$field])) {
            return $this->mainTableAlias;
        }

        return $this->fieldTableAlias[$field];
    }

    /**
     * @param string $equalFields
     *
     * @return string
     */
    public function buildEqualFields($equalFields)
    {
        $equalFields = $this->escapeFieldName($equalFields);

        $where = '';
        if (!empty($equalFields)) {
            $where .= ' AND ';
            $where .= implode($this->resolveTableAliasForParamsArray(array_keys($equalFields)), '=? AND ');
            $where .= '=?';
        }

        return $where;
    }

    /**
     * @param string $notEqualFields
     *
     * @return string
     */
    public function buildNotEqualFields($notEqualFields)
    {
        $notEqualFields = $this->escapeFieldName($notEqualFields);

        $where = '';
        if (!empty($notEqualFields)) {
            $where .= ' AND ';
            $where .= implode($this->resolveTableAliasForParamsArray(array_keys($notEqualFields)), '!=? AND ');
            $where .= '!=?';
        }

        return $where;
    }

    /**
     * @param array $inFields
     *
     * @return string
     */
    public function buildInFields(array $inFields)
    {
        $inFields = $this->escapeFieldName($inFields);

        $where = '';
        foreach ($inFields as $idx => $paramValue) {
            $where .= ' AND ' . $this->resolveTableAliasForParam($idx) . " IN ('" . implode("','", $paramValue) . "')";
        }

        return $where;
    }

    /**
     * @param array $notInFields
     *
     * @return string
     */
    public function buildNotInFields(array $notInFields)
    {
        $notInFields = $this->escapeFieldName($notInFields);

        $where = '';
        foreach ($notInFields as $idx => $paramValue) {
            $where .= ' AND ' . $this->resolveTableAliasForParam($idx) . " NOT IN ('" . implode("','", $paramValue) . "')";
        }

        return $where;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function prepareFieldsFromQueryString($params)
    {
        $fields = '*';
        if (isset($params['fields'])) {
            $fields = array();
            $selected = explode(',', $params['fields']);
            foreach ($selected as $field) {
                $fields[] = '`' . trim($field) . '`';
            }
            $fields = implode(',', $fields);
        }

        return $fields;
    }


    /**
     * @param array $params
     * @param string $groupBy
     * @param bool $noOrderClause
     *
     * @return string
     */
    public function prepareWhereClauseFromQueryString(&$params, $groupBy = '', $noOrderClause = false)
    {
        $this->notEqualFields = [];
        $this->joinNotEqualFields = [];
        $this->equalFields = [];
        $this->joinEqualFields = [];
        $sort = [];

        if (isset($params)) {

            if (isset($params['sort'])) {
                $orderByGroups = explode(',', $params['sort']);
                foreach ($orderByGroups as $group) {
                    $group = trim($group);
                    if (strstr($group, '-')) {
                        $sort[] = substr($group, 1) . ' DESC';
                    } else {
                        if (strstr($group, '+')) {
                            $sort[] = substr($group, 1) . ' ASC';
                        } else {
                            $sort[] = $group . ' ASC';
                        }
                    }
                }
            }

            if (isset($params['start'])) {
                unset($params['start']);
            }

            if (isset($params['limit'])) {
                unset($params['limit']);
            }

            if (isset($params['resolveHateoas'])) {
                unset($params['resolveHateoas']);
            }

            if (isset($params['hateoasFilters'])) {
                unset($params['hateoasFilters']);
            }

            if (isset($params['fields'])) {
                unset($params['fields']);
            }

            if (isset($params['sort'])) {
                if (!$noOrderClause) {
                    $sort = array();
                }
                unset($params['sort']);
            }

        }

        if (empty($params)) {
            $params = array();
        }

        $where = ' AND 1=1 ';

        foreach ($params as $idx => $paramValue) {

            // in / not in
            if (is_array($paramValue)) {
                if (substr($idx, -2) == '!!') {
                    $realIdxName = substr($idx, 0, -2);
                    if ($this->getTableAliasForParam($realIdxName) == $this->mainTableAlias) {
                        $this->notInFields[$realIdxName] = $paramValue;
                    } else {
                        $this->joinNotInFields[$realIdxName] = $paramValue;
                    }
                } else {
                    if ($this->getTableAliasForParam($idx) == $this->mainTableAlias) {
                        $this->inFields[$idx] = $paramValue;
                    } else {
                        $this->joinInFields[$idx] = $paramValue;
                    }
                }
                unset($params[$idx]);
            }

            // equals / not equals
            if (!is_array($paramValue)) {
                if (substr($idx, -2) == '!!') {
                    $realIdxName = substr($idx, 0, -2);
                    if ($this->getTableAliasForParam($realIdxName) == $this->mainTableAlias) {
                        $this->notEqualFields[$realIdxName] = $paramValue;
                    } else {
                        $this->joinNotEqualFields[$realIdxName] = $paramValue;
                    }

                } else {
                    if ($this->getTableAliasForParam($idx) == $this->mainTableAlias) {
                        $this->equalFields[$idx] = $paramValue;
                    } else {
                        $this->joinEqualFields[$idx] = $paramValue;
                    }

                }
            }

        }

        if (count($this->equalFields) > 0) {
            $where .= $this->buildEqualFields($this->equalFields);
        }

        if (count($this->notEqualFields) > 0) {
            $where .= $this->buildNotEqualFields($this->notEqualFields);
        }

        if (count($this->inFields) > 0) {
            $where .= $this->buildInFields($this->inFields);
        }

        if (count($this->notInFields) > 0) {
            $where .= $this->buildNotInFields($this->notInFields);
        }

        if (count($this->joinEqualFields) > 0) {
            $where .= ' AND JoinExpectedResultEquals.id IS NOT NULL';
        }

        if (count($this->joinInFields) > 0) {
            $where .= ' AND JoinExpectedResult.id IS NOT NULL';
        }

        if (count($this->joinNotInFields) > 0) {
            $where .= ' AND JoinNotExpectedResult.id IS NULL';
        }

        if (count($this->joinNotEqualFields) > 0) {
            $where .= ' AND JoinNotExpectedResultEquals.id IS NULL';
        }

        $where .= $groupBy;
        if (count($sort) >= 1) {
            $where .= ' ORDER BY ' . implode(',', $sort);
        }

        return $where;
    }

    /**
     * @return string
     */
    public function applyJoin()
    {
        $join = '';

        if (count($this->joinEqualFields) > 0) {
            $join .= $this->buildJoinEqualFields($this->joinEqualFields);
        }

        if (count($this->joinInFields) > 0) {
            $join .= $this->buildJoinInFields($this->joinInFields);
        }

        return $join;
    }

    /**
     * @return string
     */
    public function applyJoinNot()
    {
        $joinNot = '';

        if (count($this->joinNotInFields) > 0) {
            $joinNot .= $this->buildJoinNotInFields($this->joinNotInFields);
        }

        if (count($this->joinNotEqualFields) > 0) {
            $joinNot .= $this->buildJoinNotEqualFields($this->joinNotEqualFields);
        }

        return $joinNot;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function findNext($id)
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM `' . $this->table . '` WHERE deleted = 0 AND id > ? ORDER BY id ASC LIMIT 1', array((int) $id));

        return $data;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function findPrevious($id)
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM `' . $this->table . '` WHERE deleted = 0 AND id < ? ORDER BY id DESC LIMIT 1', array((int) $id));

        return $data;
    }
}
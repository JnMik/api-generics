<?php

namespace Support3w\Api\Generic\Repository;

use Doctrine\DBAL\Connection;

/**
 * Class DefaultRepository
 *
 */
class DefaultRepository extends RepositoryBase
{

    /**
     * @param Connection $connection
     * @param $table
     * @param array $fieldTableAlias
     * @param $mainTableAlias
     */
    public function __construct(Connection $connection,
                                $table,
                                array $fieldTableAlias,
                                $mainTableAlias)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->fieldTableAlias = $fieldTableAlias;
        $this->mainTableAlias = $mainTableAlias;
    }

    /**
     * @param array $joinEqualFields
     * @return mixed
     */
    public function buildJoinEqualFields(array $joinEqualFields)
    {
        // TODO: Implement buildJoinEqualFields() method.
    }

    /**
     * @param array $joinNotEqualFields
     * @return mixed
     */
    public function buildJoinNotEqualFields(array $joinNotEqualFields)
    {
        // TODO: Implement buildJoinNotEqualFields() method.
    }

    /**
     * @param array $joinInFields
     * @return mixed
     */
    public function buildJoinInFields(array $joinInFields)
    {
        // TODO: Implement buildJoinInFields() method.
    }

    /**
     * @param array $joinNotInFields
     * @return mixed
     */
    public function buildJoinNotInFields(array $joinNotInFields)
    {
        // TODO: Implement buildJoinNotInFields() method.
    }
}

<?php
namespace Mezon\CrudService;

// TODO extract model to the separate package
/**
 * Class CrudServiceModel
 *
 * @package CrudService
 * @subpackage CrudServiceModel
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Crud service's default model
 *
 * @author Dodonov A.A.
 */
class CrudServiceModel extends \Mezon\Service\DbServiceModel
{

    /**
     * Method transforms record before it will be returned with the newRecordsSince method
     *
     * @param array $records
     *            Record to be transformed
     */
    protected function lastNewRecordsSince(array &$records)
    {
        $this->getRecordsTransformer($records);
    }

    /**
     * Method adds domain conditions
     *
     * @param int|bool $domainId
     *            Do we have domain limitations
     * @param array $where
     *            where condition
     * @return array where condition with domain_id limitations
     */
    protected function addDomainIdCondition($domainId, array $where = []): array
    {
        if ($domainId === false) {
            if (count($where) === 0) {
                $where[] = '1 = 1';
            }
        } else {
            $where[] = 'domain_id = ' . intval($domainId);
        }

        return $where;
    }

    /**
     * Method returns all records created since $date
     *
     * @param int|bool $domainId
     *            Do we have domain limitations
     * @param \datetime $date
     *            Start of the period
     * @return array List of records created since $date
     */
    public function newRecordsSince($domainId, $date)
    {
        $where = $this->addDomainIdCondition($domainId);

        $where[] = 'creation_date >= "' . date('Y-m-d H:i:s', strtotime($date)) . '"';

        $connection = $this->getConnection();

        $records = $connection->select($this->getFieldsNames(), $this->getTableName(), implode(' AND ', $where));

        $this->lastNewRecordsSince($records);

        return $records;
    }

    /**
     * Method returns amount of records in table
     *
     * @param int|bool $domainId
     *            Do we have domain limitations
     * @param array $where
     *            Filter
     * @return number Amount of records
     */
    public function recordsCount($domainId = false, array $where = [
        '1=1'
    ]): int
    {
        $where = $this->addDomainIdCondition($domainId, $where);

        $records = $this->getConnection()->select(
            'COUNT( * ) AS records_count',
            $this->getTableName(),
            implode(' AND ', $where));

        if (count($records) === 0) {
            return 0;
        }

        return \Mezon\Functional\Functional::getField($records[0], 'records_count');
    }

    /**
     * Method defaults empty order to the default one
     *
     * @param array $order
     *            order data to be defaulted
     * @return array defaulted order data
     */
    protected function getDefaultOrder(array $order): array
    {
        return count($order) > 0 ? $order : [
            'field' => 'id',
            'order' => 'ASC'
        ];
    }

    /**
     * Method fetches records before transformation
     *
     * @param int|bool $domainId
     *            Id of the domain
     * @param int $from
     *            Starting record
     * @param int $limit
     *            Fetch limit
     * @param array $where
     *            Fetch condition
     * @param array $order
     *            Sorting condition
     * @return array of records
     */
    public function getSimpleRecords($domainId, $from, $limit, $where, $order = [])
    {
        $where = $this->addDomainIdCondition($domainId, $where);
        $order = $this->getDefaultOrder($order);

        return $this->getConnection()->select(
            $this->getFieldsNames(),
            $this->getTableName(),
            implode(' AND ', $where) . ' ORDER BY ' . htmlspecialchars($order['field']) . ' ' .
            htmlspecialchars($order['order']),
            $from,
            $limit);
    }

    /**
     * Method transforms record before it will be returned with the getRecords method
     *
     * @param array $records
     *            Record to be transformed
     *            
     * @codeCoverageIgnore
     */
    protected function getRecordsTransformer(array &$records)
    {}

    /**
     * Method fetches records after transformation
     *
     * @param int|bool $domainId
     *            Id of the domain
     * @param int $from
     *            Starting record
     * @param int $limit
     *            Fetch limit
     * @param array $where
     *            Fetch condition
     * @param array $order
     *            Sorting condition
     * @return array of records
     */
    public function getRecords($domainId, $from, $limit, $where = [
        '1=1'
    ], $order = [])
    {
        $records = $this->getSimpleRecords($domainId, $from, $limit, $where, $order);

        $this->getRecordsTransformer($records);

        return $records;
    }

    /**
     * Method transforms record before it will be returned with the lastRecords method
     *
     * @param array $records
     *            Record to be transformed
     */
    protected function lastRecordsTransformer(array &$records)
    {
        $this->getRecordsTransformer($records);
    }

    /**
     * Method returns last $count records
     *
     * @param int|bool $domainId
     *            Id of the domain
     * @param int $count
     *            Amount of records to be returned
     * @param array $where
     *            Filter conditions
     * @return array List of the last $count records
     */
    public function lastRecords($domainId, $count, $where)
    {
        $where = $this->addDomainIdCondition($domainId, $where);

        $records = $this->getConnection()->select(
            $this->getFieldsNames(),
            $this->getTableName(),
            implode(' AND ', $where) . ' ORDER BY id DESC',
            0,
            $count);

        $this->lastRecordsTransformer($records);

        return $records;
    }

    /**
     * Method transforms record before it will be returned with the fetchRecordsByIds method
     *
     * @param array $records
     *            Record to be transformed
     */
    protected function fetchRecordsByIdsTransformer(array &$records)
    {
        $this->getRecordsTransformer($records);
    }

    /**
     * Method fetches records bythe specified fields
     *
     * @param int|bool $domainId
     *            Domain id
     * @param string $ids
     *            ids of records to be fetched
     * @return array list of records
     */
    public function fetchRecordsByIds($domainId, string $ids)
    {
        if ($domainId === false) {
            $where = 'id IN ( ' . $ids . ' )';
        } else {
            $where = 'id IN ( ' . $ids . ' ) AND domain_id = ' . intval($domainId);
        }

        $records = $this->getConnection()->select($this->getFieldsNames(), $this->getTableName(), $where);

        if (count($records) == 0) {
            throw (new \Exception(
                'Record with id in ' . $ids . ' and domain = ' . ($domainId === false ? 'false' : $domainId) .
                ' was not found',
                - 1));
        }

        $this->fetchRecordsByIdsTransformer($records);

        return $records;
    }

    /**
     * Method returns amount of records in table, grouped by the specified field
     *
     * @param int|bool $domainId
     *            Domain id
     * @param string $fieldName
     *            Grouping field
     * @param array $where
     *            Filtration conditions
     * @return array Records with stat
     */
    public function recordsCountByField($domainId, string $fieldName, array $where): array
    {
        $where = $this->addDomainIdCondition($domainId, $where);

        $records = $this->getConnection()->select(
            $fieldName . ' , COUNT( * ) AS records_count',
            $this->getTableName(),
            implode(' AND ', $where) . ' GROUP BY ' . $fieldName);

        if (count($records) === 0) {
            return [
                'records_count' => 0
            ];
        }

        return $records;
    }

    /**
     * Method deletes filtered records
     *
     * @param mixed $domainId
     *            Domain id
     * @param array $where
     *            Filtration conditions
     */
    public function deleteFiltered($domainId, array $where)
    {
        if ($domainId === false) {
            return $this->getConnection()->delete($this->getTableName(), implode(' AND ', $where));
        } else {
            return $this->getConnection()->delete(
                $this->getTableName(),
                implode(' AND ', $where) . ' AND domain_id = ' . intval($domainId));
        }
    }

    /**
     * Method updates records
     *
     * @param
     *            int DomainId Domain id. Pass false if we want to ignore domain_id security
     * @param array $record
     *            New values for fields
     * @param array $where
     *            Condition
     * @return array Updated fields
     */
    public function updateBasicFields($domainId, array $record, array $where)
    {
        $where = $this->addDomainIdCondition($domainId, $where);

        $connection = $this->getConnection();

        $connection->update($this->getTableName(), $record, implode(' AND ', $where));

        return $record;
    }

    /**
     * Method fetches fields for model manipulation
     *
     * @return array fetched fields
     */
    public function fetchFields(): array
    {
        // TODO fields must be fetched in the CrudServiceLogic class
        $record = [];

        foreach ($this->getFields() as $name) {
            if ($this->getFieldType($name) == 'custom') {
                // you need to create your own handlers for the custom type
                continue;
            }
            if ($name == 'id' || $name == 'domain_id') {
                continue;
            }
            if ($name == 'modification_date' || $name == 'creation_date') {
                $record[$name] = 'NOW()';
                continue;
            }

            if (isset($_POST[$name])) {
                $record[$name] = \Mezon\Security\Security::getStringValue($_POST[$name]);
            }
        }

        return $record;
    }

    /**
     * Method inserts basic fields
     *
     * @param array $record
     *            Record to be inserted
     * @param mixed $domainId
     *            Id of the domain
     * @return array Inserted record
     */
    public function insertBasicFields(array $record, $domainId = 0)
    {
        if ($this->hasField('domain_id')) {
            $record['domain_id'] = $domainId;
        }

        if (count($record) === 0) {
            $msg = 'Trying to create empty record. Be shure that you have passed at least one of these fields : ';

            throw (new \Exception($msg . $this->getFieldsNames()));
        }

        $record['id'] = $this->getConnection()->insert($this->getTableName(), $record);

        return $record;
    }
}

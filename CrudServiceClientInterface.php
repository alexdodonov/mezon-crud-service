<?php
namespace Mezon\CrudService;

/**
 * Interface CrudServiceClientInterface
 *
 * @package CrudService
 * @subpackage CrudServiceClientInterface
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Interface for basic Crud API client
 *
 * @author Dodonov A.A.
 */
interface CrudServiceClientInterface
{

    /**
     * Method returns all records by filter
     *
     * @param array $filter
     *            Filtering settings
     * @param int $crossDomain
     *            Cross domain security settings
     * @return array List of records
     * @codeCoverageIgnore
     */
    public function getRecordsBy($filter, $crossDomain = 0);

    /**
     * Method returns record by it's id
     *
     * @param int $id
     *            Id of the fetching record
     * @param number $crossDomain
     *            Domain id
     * @return object fetched record
     * @codeCoverageIgnore
     */
    public function getById($id, $crossDomain = 0);

    /**
     * Method returns records by their ids
     *
     * @param array $ids
     *            List of ids
     * @param number $crossDomain
     *            Domain id
     * @return array Fetched records
     */
    public function getByIdsArray($ids, $crossDomain = 0);

    /**
     * Method creates new record
     *
     * @param array $data
     *            data for creating record
     * @return int id of the created record
     */
    public function create($data);

    /**
     * Method updates new record
     *
     * @param int $id
     *            Id of the updating record
     * @param array $data
     *            Data to be posted
     * @param int $crossDomain
     *            Cross domain policy
     * @return mixed Result of the RPC call
     * @codeCoverageIgnore
     */
    public function update(int $id, array $data, int $crossDomain = 0);

    /**
     * Method returns all records created since $date
     *
     * @param \datetime $date
     *            Start of the period
     * @return array List of records created since $date
     * @codeCoverageIgnore
     */
    public function newRecordsSince($date);

    /**
     * Method returns count of records
     *
     * @return array List of records created since $date
     * @codeCoverageIgnore
     */
    public function recordsCount();

    /**
     * Method returns last $count records
     *
     * @param int $count
     *            Amount of records to be fetched
     * @param array $filter
     *            Filter data
     * @return array $count of last created records
     * @codeCoverageIgnore
     */
    public function lastRecords($count, $filter);

    /**
     * Method deletes record with $id
     *
     * @param int $id
     *            Id of the deleting record
     * @param int $crossDomain
     *            Break domain's bounds or not
     * @return string Result of the deletion
     * @codeCoverageIgnore
     */
    public function delete(int $id, int $crossDomain = 0): string;

    /**
     * Method returns count off records
     *
     * @param string $field
     *            Field for grouping
     * @param array $filter
     *            Filtering settings
     * @return array List of records created since $date
     */
    public function recordsCountByField(string $field, $filter = false): array;

    /**
     * Method deletes records by filter
     *
     * @param int $crossDomain
     *            Cross domain security settings
     * @param array $filter
     *            Filtering settings
     * @codeCoverageIgnore
     */
    public function deleteFiltered($crossDomain = 0, $filter = false);

    /**
     * Method creates instance if the CrudServiceClient class
     *
     * @param string $service
     *            Service to be connected to
     * @param string $token
     *            Connection token
     * @return \Mezon\CrudService\CrudServiceClient Instance of the CrudServiceClient class
     */
    public static function instance(string $service, string $token): \Mezon\CrudService\CrudServiceClient;

    /**
     * Method returns some records of the user's domain
     *
     * @param int $from
     *            The beginnig of the fetching sequence
     * @param int $limit
     *            Size of the fetching sequence
     * @param int $crossDomain
     *            Cross domain security settings
     * @param array $filter
     *            Filtering settings
     * @param array $order
     *            Sorting settings
     * @return array List of records
     */
    public function getList(int $from = 0, int $limit = 1000000000, $crossDomain = 0, $filter = false, $order = false): array;

    /**
     * Method returns fields and layout
     *
     * @return array Fields and layout
     */
    public function getFields(): array;
}

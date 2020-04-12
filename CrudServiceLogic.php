<?php
namespace Mezon\CrudService;

/**
 * Class CrudServiceLogic
 *
 * @package CrudService
 * @subpackage CrudServiceLogic
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */
define('ORDER_FIELD_NAME', 'order');
define('FIELD_FIELD_NAME', 'field');

/**
 * Class handles Crud logic.
 *
 * @author Dodonov A.A.
 */
class CrudServiceLogic extends \Mezon\Service\ServiceLogic
{

    /**
     * Form builder
     */
    protected $formBuilder = false;

    /**
     * Method deletes the specified record
     *
     * @return int id of the deleted record
     */
    public function deleteRecord()
    {
        $domainId = $this->getDomainId();
        $where = \Mezon\Filter::addFilterCondition([
            'id = ' . intval($this->getParamsFetcher()->getParam('id'))
        ]);

        return $this->model->deleteFiltered($domainId, $where);
    }

    /**
     * Method deletes filtered records
     */
    public function deleteFiltered()
    {
        $domainId = $this->getDomainId();
        $where = \Mezon\Filter::addFilterCondition([]);

        return $this->model->deleteFiltered($domainId, $where);
    }

    /**
     * Method returns records
     *
     * @param int $domainId
     *            Domain id
     * @param array $order
     *            Sorting settings
     * @param int $from
     *            Starting record
     * @param int $limit
     *            Fetch limit
     * @return array of records after all transformations
     */
    public function getRecords($domainId, $order, $from, $limit): array
    {
        return $this->model->getSimpleRecords($domainId, $from, $limit, \Mezon\Filter::addFilterCondition([]), $order);
    }

    /**
     * Method returns true if the modelhas 'domain_id' field
     *
     * @return bool true if the modelhas 'domain_id' field, false otherwise
     */
    protected function hasDomainId(): bool
    {
        return $this->model->hasField('domain_id');
    }

    /**
     * Method returns domain id
     *
     * @return int Domain id
     */
    public function getDomainId()
    {
        // records are not separated between domains
        if ($this->hasDomainId() === false) {
            return false;
        }

        if (isset($_GET['cross_domain']) && intval($_GET['cross_domain'])) {
            if ($this->hasPermit($this->model->getEntityName() . '-manager')) {
                $domainId = false;
            } else {
                throw (new \Exception('User "' . $this->getSelfLoginValue() . '" has no permit "' . $this->model->getEntityName() . '-manager"'));
            }
        } else {
            $domainId = $this->getSelfIdValue();
        }

        return $domainId;
    }

    /**
     * Method returns records
     *
     * @return array of records after all transformations
     */
    public function listRecord(): array
    {
        $domainId = $this->getDomainId();
        $order = $this->getParamsFetcher()->getParam(ORDER_FIELD_NAME, [
            FIELD_FIELD_NAME => 'id',
            ORDER_FIELD_NAME => 'ASC'
        ]);

        $from = $this->getParamsFetcher()->getParam('from', 0);
        $limit = $this->getParamsFetcher()->getParam('limit', 1000000000);

        return $this->getRecords($domainId, $order, $from, $limit);
    }

    /**
     * Method returns all records
     *
     * @return array of records after all transformations
     */
    public function all(): array
    {
        $domainId = $this->getDomainId();
        $order = $this->getParamsFetcher()->getParam(ORDER_FIELD_NAME, [
            FIELD_FIELD_NAME => 'id',
            ORDER_FIELD_NAME => 'ASC'
        ]);

        return $this->getRecords($domainId, $order, 0, 1000000000);
    }

    /**
     * Method returns all records created since $date
     *
     * @return array List of records created since $date
     */
    public function newRecordsSince(): array
    {
        $domainId = $this->getDomainId();
        $date = $this->getParamsFetcher()->getParam('date');

        if ($this->model->hasField('creation_date') === false) {
            throw (new \Exception('Field "creation_date" was not found'));
        }

        return $this->model->newRecordsSince($domainId, $date);
    }

    /**
     * Method returns records count
     *
     * @return int Records count
     */
    public function recordsCount(): int
    {
        $domainId = $this->getDomainId();

        return $this->model->recordsCount($domainId);
    }

    /**
     * Method returns last $count records
     *
     * @return array List of the last $count records
     */
    public function lastRecords()
    {
        $domainId = $this->getDomainId();
        $count = $this->getParamsFetcher()->getParam('count');
        $filter = \Mezon\Filter::addFilterCondition([
            '1 = 1'
        ]);

        return $this->model->lastRecords($domainId, $count, $filter);
    }

    /**
     * Method compiles basic update record
     *
     * @param int $id
     *            Id of the updating record
     * @return array with updated fields
     */
    protected function updateBasicFields($id)
    {
        $domainId = $this->getDomainId();
        $record = $this->model->fetchFields();

        if ($this->hasDomainId()) {
            $record['domain_id'] = $this->getSelfIdValue();
        }

        $where = [
            "id = " . $this->getParam('id')
        ];

        return $this->model->updateBasicFields($domainId, $record, $where);
    }

    /**
     * Method updates custom fields
     *
     * @param int $id
     *            Id of the updating record
     * @param array|object $record
     *            Updating data
     * @param array $customFields
     *            Custom fields to be updated
     * @return array|object - Updated data
     */
    protected function updateCustomFields($id, $record, $customFields)
    {
        if (isset($customFields)) {
            foreach ($customFields as $name => $value) {
                $this->model->setFieldForObject($id, $name, $value);
            }

            $record['custom_fields'] = $customFields;
        }

        return $record;
    }

    /**
     * Method updates record and it's custom fields
     *
     * @return array Updated fields and their new values
     */
    public function updateRecord()
    {
        $id = $this->getParamsFetcher()->getParam('id');

        $record = $this->updateBasicFields($id);

        $record = $this->updateCustomFields($id, $record, $this->getParamsFetcher()->getParam('custom_fields', null));

        $record['id'] = $id;

        return $record;
    }

    /**
     * Method creates user
     *
     * @return array Created record
     */
    public function createRecord()
    {
        $record = $this->model->fetchFields();

        if ($this->hasDomainId()) {
            $domainId = $this->getSelfIdValue();
        } else {
            $domainId = false;
        }

        $record = $this->model->insertBasicFields($record, $domainId);

        foreach ($this->model->getFields() as $name) {
            $fieldName = $this->model->getEntityName() . '-' . $name;
            if ($this->model->getFieldType($name) == 'external' && $this->getParamsFetcher()->getParam($fieldName, false) !== false) {
                $ids = $this->getParamsFetcher()->getParam($fieldName);
                $record = $this->model->insertExternalFields($record, $this->getParamsFetcher()->getParam('session_id'), $name, $field, $ids);
            }
        }

        return $record;
    }

    /**
     * Method returns exact record from the table
     *
     * @return array Exact record
     */
    public function exact()
    {
        $id = $this->getParamsFetcher()->getParam('id');
        $domainId = $this->getDomainId();

        $records = $this->model->fetchRecordsByIds($domainId, $id);

        return $records[0];
    }

    /**
     * Method returns exact records from the table.
     *
     * @return array Exact list of records.
     */
    public function exactList()
    {
        $ids = $this->getParamsFetcher()->getParam('ids');
        $domainId = $this->getDomainId();

        return $this->model->fetchRecordsByIds($domainId, $ids);
    }

    /**
     * Method returns records count, grouped by the specified field.
     *
     * @return int Records count.
     */
    public function recordsCountByField()
    {
        $domainId = $this->getDomainId();

        $this->model->validateFieldExistance($this->getParamsFetcher()->getParam(FIELD_FIELD_NAME));

        $field = \Mezon\Security\Security::getStringValue($this->getParamsFetcher()->getParam(FIELD_FIELD_NAME));

        $where = \Mezon\Filter::addFilterCondition([]);

        return $this->model->recordsCountByField($domainId, $field, $where);
    }

    /**
     * Fields descriptions
     *
     * @return array Fields descriptions
     */
    public function fields(): array
    {
        return [
            'fields' => $this->model->getFields()
        ];
    }
}

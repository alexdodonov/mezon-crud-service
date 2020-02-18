<?php
namespace Mezon\CrudService;

/**
 * Class CrudService
 *
 * @package Mezon
 * @subpackage CrudService
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Class for custom crud service.
 *
 * @author Dodonov A.A.
 */
class CrudService extends \Mezon\Service\Service
{

    /**
     * Constructor
     *
     * @param array $entity
     *            Entity description
     * @param mixed $serviceTransport
     *            Service's transport, defaulted to \Mezon\Service\ServiceRestTransport::class
     * @param mixed $securityProvider
     *            Service's security provider, defaulted to \Mezon\Service\ServiceMockSecurityProvider::class
     * @param mixed $serviceLogic
     *            Service's logic, defaulted to \Mezon\CrudService\CrudServiceLogic::class
     * @param mixed $serviceModel
     *            Service's model, defaulted to \Mezon\CrudService\CrudServiceModel::class
     */
    public function __construct(
        array $entity,
        $serviceTransport = \Mezon\Service\ServiceRestTransport\ServiceRestTransport::class,
        $securityProvider = \Mezon\Service\ServiceMockSecurityProvider::class,
        $serviceLogic = \Mezon\CrudService\CrudServiceLogic::class,
        $serviceModel = \Mezon\CrudService\CrudServiceModel::class)
    {
        try {
            parent::__construct(
                $serviceTransport,
                $securityProvider,
                $serviceLogic,
                $this->initModel($entity, $serviceModel));

            $this->initCrudRoutes();
        } catch (\Exception $e) {
            $this->serviceTransport->handleException($e);
        }
    }

    /**
     * Method inits service's model
     *
     * @param array $entity
     *            Entity description
     * @param string|\Mezon\CrudService\CrudServiceModel $serviceModel
     *            Service's model
     */
    protected function initModel(array $entity, $serviceModel)
    {
        $fields = isset($entity['fields']) ? $entity['fields'] : $this->getFieldsFromConfig();

        if (is_string($serviceModel)) {
            $this->model = new $serviceModel($fields, $entity['table-name'], $entity['entity-name']);
        } else {
            $this->model = $serviceModel;
        }

        return $this->model;
    }

    /**
     * Method returns fields from config
     *
     * @return array List of fields
     */
    protected function getFieldsFromConfig()
    {
        if (file_exists('./conf/fields.json')) {
            return json_decode(file_get_contents('./conf/fields.json'), true);
        }

        throw (new \Exception('fields.json was not found'));
    }

    /**
     * Method inits common servoce's routes
     */
    protected function initCrudRoutes(): void
    {
        $this->serviceTransport->addRoute('/list/', 'listRecord', 'GET');
        $this->serviceTransport->addRoute('/all/', 'all', 'GET');
        $this->serviceTransport->addRoute('/exact/list/[il:ids]/', 'exactList', 'GET');
        $this->serviceTransport->addRoute('/exact/[i:id]/', 'exact', 'GET');
        $this->serviceTransport->addRoute('/fields/', 'fields', 'GET');
        $this->serviceTransport->addRoute('/delete/[i:id]/', 'deleteRecord', ['POST','DELETE']);
        $this->serviceTransport->addRoute('/delete/', 'deleteFiltered', ['POST','DELETE']);
        $this->serviceTransport->addRoute('/create/', 'createRecord', ['POST','PUT']);
        $this->serviceTransport->addRoute('/update/[i:id]/', 'updateRecord', 'POST');
        $this->serviceTransport->addRoute('/new/from/[s:date]/', 'newRecordsSince', 'GET');
        $this->serviceTransport->addRoute('/records/count/', 'recordsCount', 'GET');
        $this->serviceTransport->addRoute('/last/[i:count]/', 'lastRecords', 'GET');
        $this->serviceTransport->addRoute('/records/count/[s:field]/', 'recordsCountByField', 'GET');

        // TODO allow in CrudServiceClient trait DELETE and PUT as POST. Or ServiceClient? Yeah ServiceClient is much better candidate )
    }
}

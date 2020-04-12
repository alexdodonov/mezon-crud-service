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
     * @param mixed $serviceLogic
     *            Service's logic, defaulted to \Mezon\CrudService\CrudServiceLogic::class
     * @param mixed $serviceModel
     *            Service's model, defaulted to \Mezon\CrudService\CrudServiceModel::class
     * @param mixed $securityProvider
     *            Service's security provider, defaulted to \Mezon\Service\ServiceMockSecurityProvider::class
     * @param mixed $serviceTransport
     *            Service's transport, defaulted to \Mezon\Service\ServiceRestTransport::class
     */
    public function __construct(
        array $entity,
        $serviceLogic = \Mezon\CrudService\CrudServiceLogic::class,
        $serviceModel = \Mezon\CrudService\CrudServiceModel::class,
        $securityProvider = \Mezon\Service\ServiceMockSecurityProvider::class,
        $serviceTransport = \Mezon\Service\ServiceRestTransport\ServiceRestTransport::class)
    {
        try {
            parent::__construct(
                $serviceLogic,
                $this->initModel($entity, $serviceModel),
                $securityProvider,
                $serviceTransport
            );

            $this->initCrudRoutes();
        } catch (\Exception $e) {
            $this->getTransport()->handleException($e);
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
        $reflector = new \ReflectionClass(get_class($this));
        $classPath = dirname($reflector->getFileName());

        if (file_exists($classPath . '/conf/fields.json')) {
            return json_decode(file_get_contents($classPath . '/conf/fields.json'), true);
        }

        throw (new \Exception('fields.json was not found'));
    }

    /**
     * Method inits common servoce's routes
     */
    protected function initCrudRoutes(): void
    {
        $this->getTransport()->addRoute('/list/', 'listRecord', 'GET');
        $this->getTransport()->addRoute('/all/', 'all', 'GET');
        $this->getTransport()->addRoute('/exact/list/[il:ids]/', 'exactList', 'GET');
        $this->getTransport()->addRoute('/exact/[i:id]/', 'exact', 'GET');
        $this->getTransport()->addRoute('/fields/', 'fields', 'GET');
        $this->getTransport()->addRoute('/delete/[i:id]/', 'deleteRecord', [
            'POST',
            'DELETE'
        ]);
        $this->getTransport()->addRoute('/delete/', 'deleteFiltered', [
            'POST',
            'DELETE'
        ]);
        $this->getTransport()->addRoute('/create/', 'createRecord', [
            'POST',
            'PUT'
        ]);
        $this->getTransport()->addRoute('/update/[i:id]/', 'updateRecord', 'POST');
        $this->getTransport()->addRoute('/new/from/[s:date]/', 'newRecordsSince', 'GET');
        $this->getTransport()->addRoute('/records/count/', 'recordsCount', 'GET');
        $this->getTransport()->addRoute('/last/[i:count]/', 'lastRecords', 'GET');
        $this->getTransport()->addRoute('/records/count/[s:field]/', 'recordsCountByField', 'GET');

        // TODO allow in CrudServiceClient trait DELETE and PUT as POST. Or ServiceClient? Yeah ServiceClient is much better candidate )
    }
}

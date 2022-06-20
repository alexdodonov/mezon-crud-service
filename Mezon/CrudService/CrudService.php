<?php
namespace Mezon\CrudService;

use Mezon\Service\Service;
use Mezon\Service\TransportInterface;

/**
 * Class CrudService
 *
 * @package Mezon
 * @subpackage CrudService
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, http://aeon.su
 */

/**
 * Class for custom crud service.
 *
 * @author Dodonov A.A.
 */
class CrudService extends Service
{

    /**
     * Constructor
     *
     * @param TransportInterface $serviceTransport
     *            service's transport
     */
    public function __construct(TransportInterface $serviceTransport)
    {
        try {
            parent::__construct($serviceTransport);

            $this->initCrudRoutes();
        } catch (\Exception $e) {
            $this->getTransport()->handleException($e);
        }
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
    }
}

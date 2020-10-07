<?php
namespace Mezon\CrudService\Tests;

use Mezon\CrudService\CrudServiceLogic;
use Mezon\CrudService\CrudServiceModel;
use Mezon\CrudService\CrudService;
use Mezon\Service\Transport;

/**
 * Class CrudServiceExceptionConstructorMock
 *
 * @package CrudService
 * @subpackage CrudServiceUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */

class CrudServiceExceptionConstructorMock extends CrudService
{

    public function __construct(Transport $transport)
    {
        parent::__construct([
            'fields' => '*',
            'table-name' => 'table',
            'entity-name' => 'entity'
        ], CrudServiceLogic::class, CrudServiceModel::class, new FakeSecurityProviderForCrudService(), $transport);
    }

    protected function initCommonRoutes(): void
    {
        throw (new \Exception('Testing exception'));
    }
}

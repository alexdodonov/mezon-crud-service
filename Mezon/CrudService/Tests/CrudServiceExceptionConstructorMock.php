<?php
namespace Mezon\CrudService\Tests;

use Mezon\CrudService\CrudService;

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

    protected function initCrudRoutes(): void
    {
        throw (new \Exception('Testing exception'));
    }
}

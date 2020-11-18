<?php
namespace Mezon\CrudService\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class CreateRecordLogicUnitTests
 *
 * @package CrudServiceLogic
 * @subpackage CreateRecordLogicUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2020/11/18)
 * @copyright Copyright (c) 2020, aeon.org
 */

/**
 * Create record logic unit tests
 */
class CreateRecordLogicUnitTest extends TestCase
{

    use CrudServiceLogicTestsTrait;

    /**
     * Method tests creation
     */
    public function testCreateRecord(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock([
            'insertBasicFields'
        ]);
        $serviceModel->expects($this->once())
            ->method('insertBasicFields');

        $mock = $this->getServiceLogic($serviceModel);

        // test body and assertions
        $mock->createRecord();
    }

    /**
     * Method tests creation when domain_id is not defined
     */
    public function testCreateRecordNoDomainId(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock([
            'insertBasicFields', 'hasField'
        ]);
        $serviceModel->expects($this->once())
            ->method('insertBasicFields');
        $serviceModel->method('hasField')->willReturn(false);

        $mock = $this->getServiceLogic($serviceModel);

        // test body and assertions
        $mock->createRecord();
    }
}

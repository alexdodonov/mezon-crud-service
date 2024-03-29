<?php
namespace Mezon\CrudService\Tests;

use Mezon\Service\Tests\ServiceLogicUnitTests;
use Mezon\CrudService\CrudServiceLogic;
use Mezon\Service\ServiceHttpTransport\ServiceHttpTransport;
use Mezon\Security\MockProvider;
use Mezon\Transport\RequestParamsInterface;
use Mezon\Service\ServiceModel;

/**
 * Class CrudServiceLogicUnitTests
 *
 * @package CrudServiceLogic
 * @subpackage CrudServiceLogicUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, http://aeon.su
 */

/**
 * Common CrudServiceLogic unit tests
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CrudServiceTests extends ServiceLogicUnitTests
{

    use CrudServiceLogicTestsTrait;

    /**
     * Testing class name
     *
     * @var string
     */
    protected $className = CrudServiceLogic::class;

    /**
     * Testing getting amount of records
     */
    public function testRecordsCount1(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('recordsCount')->willReturn(1);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $count = $serviceLogic->recordsCount();

        // assertions
        $this->assertEquals(1, $count, 'Records count was not fetched');
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCount0(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('recordsCount')->willReturn(0);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $count = $serviceLogic->recordsCount();

        // assertions
        $this->assertEquals(0, $count, 'Records count was not fetched');
    }

    /**
     * Method tests last N records returning
     */
    public function testLastRecords(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('lastRecords')->willReturn([
            []
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $records = $serviceLogic->lastRecords();

        // assertions
        $this->assertEquals(1, count($records), 'Invalid amount of records was returned');
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCountByExistingField(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('recordsCountByField')->willReturn([
            [
                'records_count' => 1
            ]
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        global $argv;
        $argv['field'] = 'id';

        // test body
        /** @var array<int, array{records_count: int}> $counters */
        $counters = $serviceLogic->recordsCountByField();

        // assertions
        $this->assertCount(1, $counters, 'Records were not fetched. Params:  ' . serialize($argv));
        $this->assertEquals(1, $counters[0]['records_count']);
    }

    /**
     * Testing getting amount of records.
     */
    public function testRecordsCountByNotExistingField(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();

        $serviceLogic = $this->getServiceLogic($serviceModel);

        global $argv;
        $argv['field'] = 'unexisting';

        // test body and assertions
        $this->expectException(\Exception::class);

        $serviceLogic->recordsCountByField();
    }

    /**
     * Testing constructor.
     */
    public function testConstruct(): void
    {
        $serviceTransport = new ServiceHttpTransport();
        $serviceLogic = new CrudServiceLogic(
            $serviceTransport->getParamsFetcher(),
            new MockProvider(),
            new ServiceModel());

        $this->assertInstanceOf(RequestParamsInterface::class, $serviceLogic->getParamsFetcher());
        $this->assertInstanceOf(MockProvider::class, $serviceLogic->getSecurityProvider());
    }

    /**
     * Testing records list generation
     */
    public function testListRecord(): void
    {
        // setup
        $serviceLogic = $this->setupLogicForListMethodsTesting();

        // test body
        $recordsList = $serviceLogic->listRecord();

        // assertions
        $this->assertEquals(2, count($recordsList), 'Invalid records list was fetched');
    }

    /**
     * Testing domain_id fetching
     */
    public function testGetDomainIdCrossDomainDisabled(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('hasField')->willReturn(true);

        $serviceLogic = $this->getServiceLogicMock($serviceModel);
        $serviceLogic->method('getSelfIdValue')->willReturn(1);

        unset($_GET['cross_domain']);

        // test body
        $result = $serviceLogic->getDomainId();

        // assertions
        $this->assertEquals(1, $result, 'Invalid getDomainId result. Must be 1');
    }

    /**
     * Testing domain_id fetching
     */
    public function testGetDomainIdCrossDomainEnabled(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();

        $serviceLogic = $this->getServiceLogic($serviceModel);

        $_GET['cross_domain'] = 1;

        // test
        $result = $serviceLogic->getDomainId();

        $this->assertEquals(false, $result, 'Invalid getDomainId result. Must be false');
    }

    /**
     * Testing newRecordsSince method for invalid
     */
    public function testNewRecordsSinceInvalid(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('hasField')->willReturn(false);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $this->expectException(\Exception::class);

        $serviceLogic->newRecordsSince();
    }

    /**
     * Testing newRecordsSince method
     */
    public function testNewRecordsSince(): void
    {
        // setup
        $_GET['cross_domain'] = 1;
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('hasField')->willReturn(true);
        $serviceModel->method('newRecordsSince')->willReturn([
            []
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $result = $serviceLogic->newRecordsSince();

        // assertions
        $this->assertCount(1, $result);
    }

    /**
     * Testing 'updateRecord' method
     */
    public function testUpdateRecord(): void
    {
        // setup
        $fieldName = 'record-title';
        $serviceModel = $this->getServiceModelMock([
            'updateBasicFields',
            'setFieldForObject'
        ]);
        $serviceModel->method('updateBasicFields')->willReturn([
            $fieldName => 'Record title'
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        global $argv;
        $argv[$fieldName] = 'Some title';
        $argv['custom_fields'] = [];
        $argv['custom_fields']['record-balance'] = 123;

        // test body
        $record = $serviceLogic->updateRecord();

        // assertions
        $this->assertEquals('Record title', $record[$fieldName], 'Invalid update result' . serialize($argv));
        $this->assertEquals(123, $record['custom_fields']['record-balance'], 'Invalid update result' . serialize($argv));
        $this->assertTrue(isset($record['id']), 'Id was not returned' . serialize($argv));
    }

    /**
     * Method tests filtered deletion
     */
    public function testDeleteFltered(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->expects($this->once())
            ->method('deleteFiltered');

        $mock = $this->getServiceLogic($serviceModel);

        // test body and assertions
        $mock->deleteFiltered();
    }

    /**
     * Method tests deletion
     */
    public function testDeleteRecord(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->expects($this->once())
            ->method('deleteFiltered');

        $mock = $this->getServiceLogic($serviceModel);

        // test body and assertions
        $mock->deleteRecord();
    }

    /**
     * Testing all records generation
     */
    public function testAll(): void
    {
        // setup
        $serviceLogic = $this->setupLogicForListMethodsTesting();

        // test body
        $recordsList = $serviceLogic->all();

        // assertions
        $this->assertEquals(2, count($recordsList), 'Invalid records list was fetched');
    }

    /**
     * Testing all records generation with no permits
     */
    public function testAllNoPermit(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('hasField')->willReturn(true);
        $_GET['cross_domain'] = 1;

        $serviceLogic = $this->getServiceLogicMock($serviceModel);
        $serviceLogic->method('hasPermit')->willReturn(false);

        // assertions
        $this->expectException(\Exception::class);

        // test body
        $serviceLogic->all();
    }

    /**
     * Testing 'fields' method
     */
    public function testFields(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('getFields')->willReturn([
            'id' => [
                'type' => 'integer'
            ]
        ]);

        $serviceLogic = $this->getServiceLogicMock($serviceModel);

        // test body
        $result = $serviceLogic->fields();

        // assertions
        $this->assertTrue(is_array($result));
        $this->assertTrue(is_array($result['fields']));
    }

    /**
     * Testing 'exact' method
     */
    public function testExact(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('fetchRecordsByIds')->willReturn([
            [
                'id' => 1
            ]
        ]);
        $_GET['id'] = 1;
        $serviceLogic = $this->getServiceLogicMock($serviceModel);

        // test body
        $result = $serviceLogic->exact();

        // assertions
        $this->assertEquals(1, $result['id']);
    }

    /**
     * Testing 'exactList' method
     */
    public function testExactList(): void
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('fetchRecordsByIds')->willReturn([
            [
                'id' => 1
            ],
            [
                'id' => 2
            ]
        ]);
        $_GET['id'] = 1;
        $serviceLogic = $this->getServiceLogicMock($serviceModel);

        // test body
        $result = $serviceLogic->exactList();

        // assertions
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }
}

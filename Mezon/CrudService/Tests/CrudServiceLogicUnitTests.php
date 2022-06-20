<?php
namespace Mezon\CrudService\Tests;

use Mezon\Service\Tests\ServiceLogicUnitTests;
use Mezon\CrudService\CrudServiceLogic;
use Mezon\Service\ServiceHttpTransport\ServiceHttpTransport;
use Mezon\Security\MockProvider;
use Mezon\Transport\RequestParamsInterface;
use Mezon\Service\ServiceModel;
use Mezon\PdoCrud\Tests\PdoCrudMock;
use Mezon\CrudService\CrudServiceModel;
use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;

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
class CrudServiceLogicUnitTests extends ServiceLogicUnitTests
{

    use CrudServiceLogicTestsTrait;

    /**
     *
     * {@inheritdoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        Conf::setConfigStringValue('headers/layer', 'mock');
    }

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
        $connection = new PdoCrudMock();
        $connection->selectResults[] = [
            [
                'records_count' => 1
            ]
        ];
        $_GET['field'] = 'title';
        $model = new CrudServiceModel($this->jsonData('Model'));
        $model->setConnection($connection);
        $serviceLogic = $this->getServiceLogicForModel($model);

        // test body
        /** @var array<int, array{records_count: int}> $counters */
        $counters = $serviceLogic->recordsCountByField();

        // assertions
        $this->assertCount(1, $counters);
        $this->assertEquals(1, $counters[0]['records_count'], 'Records were not counted');
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

        // assertions
        $this->expectException(\Exception::class);

        // test body
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
            $securityProvider = new MockProvider(),
            new ServiceModel());

        $this->assertInstanceOf(RequestParamsInterface::class, $serviceLogic->getParamsFetcher());
        $this->assertInstanceOf(MockProvider::class, $securityProvider);
    }

    /**
     * Testing records list generation
     */
    public function testListRecord(): void
    {
        // setup
        $connection = new PdoCrudMock();
        $connection->selectResults[] = [
            [],
            [],
            []
        ];
        $serviceLogic = $this->getServiceLogicForConnection($connection);

        // test body
        $recordsList = $serviceLogic->listRecord();

        // assertions
        $this->assertCount(3, $recordsList);
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

        // assertions
        $this->expectException(\Exception::class);

        // test body
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
        $connection = new PdoCrudMock();
        unset($_POST);
        $_GET['id'] = 1;
        $_GET['title'] = 'Record title';

        $model = new CrudServiceModel($this->jsonData('UpdateRecord'));
        $model->setConnection($connection);
        $serviceLogic = $this->getServiceLogicForModel($model);

        // test body
        $record = $serviceLogic->updateRecord();

        // assertions
        $this->assertEquals('Record title', $record['title']);
        $this->assertTrue(isset($record['id']));
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
        $connection = new PdoCrudMock();
        $connection->selectResults[] = [
            [],
            []
        ];
        $serviceLogic = $this->getServiceLogicForConnection($connection);

        // test body and assertions
        $this->assertCount(2, $serviceLogic->all());
    }

    /**
     * Testing 'fields' method
     */
    public function testFields(): void
    {
        // setup
        $serviceLogic = $this->getServiceLogicForConnection(new PdoCrudMock());

        // test body
        $result = $serviceLogic->fields();

        // assertions
        $this->assertTrue(is_array($result['fields']));
    }

    /**
     * Testing 'exact' method
     */
    public function testExact(): void
    {
        // setup
        $_GET['id'] = 1;

        $serviceLogic = $this->getServiceLogicForConnection($this->getSelectingConnection([
            [
                'id' => 1
            ]
        ]));

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
        $_GET['id'] = 1;

        $serviceLogic = $this->getServiceLogicForConnection(
            $this->getSelectingConnection([
                [
                    'id' => 1
                ],
                [
                    'id' => 2
                ]
            ]));

        // test body
        $result = $serviceLogic->exactList();

        // assertions
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }
}

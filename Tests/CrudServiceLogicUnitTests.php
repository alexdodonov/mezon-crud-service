<?php
namespace Mezon\CrudService\Tests;

/**
 * Class CrudServiceLogicUnitTests
 *
 * @package CrudServiceLogic
 * @subpackage CrudServiceLogicUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Common CrudServiceLogic unit tests
 *
 * @group baseTests
 */
class CrudServiceLogicUnitTests extends \Mezon\Service\Tests\ServiceLogicUnitTests
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(\Mezon\CrudService\CrudServiceLogic::class);
    }

    /**
     * Method returns service model
     *
     * @param array $methods
     *            Methods to be mocked
     * @return object Service model
     */
    protected function getServiceModelMock(
        array $methods = [
            'lastRecords',
            'recordsCount',
            'deleteFiltered',
            'insertBasicFields',
            'getSimpleRecords',
            'getConnection',
            'recordsCountByField',
            'newRecordsSince',
            'updateBasicFields',
            'setFieldForObject',
            'hasField'
        ])
    {
        return $this->getMockBuilder(\Mezon\CrudService\CrudServiceModel::class)
            ->setConstructorArgs(
            [
                [
                    'id' => [
                        'type' => 'integer'
                    ],
                    'domain_id' => [
                        'type' => 'integer'
                    ],
                    'creation_date' => [
                        'type' => 'date'
                    ]
                ],
                'record'
            ])
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Returning json file content
     *
     * @param string $fileName
     *            File name
     * @return array json decoded countent of the file
     */
    protected function jsonData(string $fileName): array
    {
        return json_decode(file_get_contents(__DIR__ . '/conf/' . $fileName . '.json'), true);
    }

    /**
     * Method creates full functional CrudServiceLogic object
     *
     * @param mixed $model
     *            List of models or single model
     * @return \Mezon\CrudService\CrudServiceLogic object
     */
    protected function getServiceLogic($model): \Mezon\CrudService\CrudServiceLogic
    {
        $transport = new \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport();

        return new \Mezon\CrudService\CrudServiceLogic(
            $transport->paramsFetcher,
            new \Mezon\Service\ServiceMockSecurityProvider(),
            $model);
    }

    /**
     * Method creates mock of the CrudServiceLogic object
     *
     * @param mixed $model
     *            List of models or single model
     * @return object mock object
     */
    protected function getServiceLogicMock($model): object
    {
        $transport = new \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport();

        return $this->getMockBuilder(\Mezon\CrudService\CrudServiceLogic::class)
            ->setConstructorArgs(
            [
                $transport->paramsFetcher,
                new \Mezon\Service\ServiceMockSecurityProvider(),
                $model
            ])
            ->setMethods([
            'getSelfIdValue',
            'hasPermit'
        ])
            ->getMock();
    }

    /**
     * Method creates service logic for list methods testing
     */
    protected function setupLogicForListMethodsTesting()
    {
        $connection = $this->getMockBuilder(\Mezon\PdoCrud\PdoCrud::class)
            ->disableOriginalConstructor()
            ->setMethods([
            'select'
        ])
            ->getMock();
        $connection->method('select')->willReturn([
            [
                'field_name' => 'balance',
                'field_value' => 100
            ]
        ]);

        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('getSimpleRecords')->willReturn($this->jsonData('GetSimpleRecords'));
        $serviceModel->method('getConnection')->willReturn($connection);

        return $this->getServiceLogic($serviceModel);
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCount1()
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
    public function testRecordsCount0()
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
    public function testLastRecords()
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->method('lastRecords')->willReturn([
            []
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        // test body
        $records = $serviceLogic->lastRecords(1);

        // assertions
        $this->assertEquals(1, count($records), 'Invalid amount of records was returned');
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCountByExistingField()
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
        $counters = $serviceLogic->recordsCountByField();

        // assertions
        $this->assertEquals(1, count($counters), 'Records were not fetched. Params:  ' . serialize($argv));
        $this->assertEquals(1, $counters[0]['records_count'], 'Records were not counted');
    }

    /**
     * Testing getting amount of records.
     */
    public function testRecordsCountByNotExistingField()
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
    public function testConstruct()
    {
        $serviceTransport = new \Mezon\Service\ServiceHttpTransport\ServiceHttpTransport();
        $serviceLogic = new \Mezon\CrudService\CrudServiceLogic(
            $serviceTransport->getParamsFetcher(),
            new \Mezon\Service\ServiceMockSecurityProvider());

        $this->assertInstanceOf(\Mezon\Service\ServiceRequestParamsInterface::class, $serviceLogic->getParamsFetcher());
        $this->assertInstanceOf(\Mezon\Service\ServiceMockSecurityProvider::class, $serviceLogic->getSecurityProvider());
    }

    /**
     * Testing records list generation
     */
    public function testListRecord()
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
    public function testGetDomainIdCrossDomainDisabled()
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
    public function testGetDomainIdCrossDomainEnabled()
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
    public function testNewRecordsSinceInvalid()
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
    public function testNewRecordsSince()
    {
        // setup
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
    public function testUpdateRecord()
    {
        // setup
        $fieldName = 'record-title';
        $serviceModel = $this->getServiceModelMock(['updateBasicFields','setFieldForObject']);
        $serviceModel->method('updateBasicFields')->willReturn([
            $fieldName => 'Record title'
        ]);

        $serviceLogic = $this->getServiceLogic($serviceModel);

        global $argv;
        $argv[$fieldName] = 'Some title';
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
    public function testDeleteFltered()
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
    public function testDeleteRecord()
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
    public function testAll()
    {
        // setup
        $serviceLogic = $this->setupLogicForListMethodsTesting();

        // test body
        $recordsList = $serviceLogic->all();

        // assertions
        $this->assertEquals(2, count($recordsList), 'Invalid records list was fetched');
    }

    /**
     * Method tests creation
     */
    public function testCreateRecord()
    {
        // setup
        $serviceModel = $this->getServiceModelMock();
        $serviceModel->expects($this->once())
            ->method('insertBasicFields');

        $mock = $this->getServiceLogic($serviceModel);

        // test body and assertions
        $mock->createRecord();
    }

    /**
     * Testing all records generation with no permits
     */
    public function testAllNoPermit()
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
}

<?php

class CrudServiceModelUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Method returns mock of the DB connection
     *
     * @param int $mode
     *            - Mock creation mode
     * @param array $methods
     *            - Methods tto be mocked
     * @return object Mock of the connection
     */
    protected function getConnectionMock_old(int $mode, array $methods = [
        'select'
    ])
    {
        $mock = $this->getMockBuilder(\Mezon\PdoCrud\PdoCrud::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        switch ($mode) {
            case 0:
                $mock->method('select')->willReturn([]);
                break;
            case 1:
                $mock->method('select')->willReturn([
                    [
                        'records_count' => 1
                    ]
                ]);
                break;
            case 2:
                $mock->method('select')->willReturn([
                    [],
                    []
                ]);
                break;
            case 3:
                $mock->expects($this->once())
                    ->method('insert')
                    ->willReturn(1);
                break;
            case 4:
                $mock->expects($this->once())
                    ->method('delete')
                    ->willReturn(1);
                break;
        }

        return $mock;
    }

    /**
     * Method returns mock of the DB connection
     *
     * @return object Mock of the connection
     */
    protected function getConnectionMock()
    {
        $mock = $this->getMockBuilder(\Mezon\PdoCrud\PdoCrud::class)
            ->disableOriginalConstructor()
            ->setMethods([
            'select',
            'delete',
            'update',
            'insert'
        ])
            ->getMock();

        return $mock;
    }

    /**
     * Method returns model's mock
     *
     * @param object $connectionMock
     *            Mock of the connection
     * @return object Mock of the model
     */
    protected function getModelMock($connectionMock)
    {
        $mock = $this->getMockBuilder(\Mezon\CrudService\CrudServiceModel::class)
            ->setConstructorArgs([
            [
                'id' => [
                    'type' => 'integer'
                ]
            ],
            'table-name'
        ])
            ->setMethods([
            'getConnection',
            'getRecordsTransformer'
        ])
            ->getMock();

        $mock->method('getConnection')->willReturn($connectionMock);

        return $mock;
    }

    /**
     * Method is testing default value return for empty table
     */
    public function testRecordsCount0()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())
            ->method('select')
            ->willReturn([]);
        $mock = $this->getModelMock($connection);

        // test body and asssertions
        $this->assertEquals(0, $mock->recordsCount(), 'Invalid error was returned');
    }

    /**
     * Method is testing default value return for empty table
     */
    public function testRecordsCount1()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())
            ->method('select')
            ->willReturn([
            [
                'records_count' => 1
            ]
        ]);
        $mock = $this->getModelMock($connection);

        // test body and assertions
        $this->assertEquals(1, $mock->recordsCount(), 'Invalid error was returned');
    }

    /**
     * Method tests insertBasicFields method
     */
    public function testInsertBasicFields()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())
            ->method('insert')
            ->willReturn(1);
        $mock = $this->getModelMock($connection);

        // test body
        $result = $mock->insertBasicFields([
            'title' => 'title'
        ]);

        // assertions
        $this->assertTrue(isset($result['id']), 'Invalid record was returned');
        $this->assertTrue(isset($result['title']), 'Invalid record was returned');
    }

    /**
     * Data provider for the testDeleteFiltered
     *
     * @return array Data
     */
    public function deleteFilteredTestData(): array
    {
        return [
            [
                false
            ],
            [
                1
            ]
        ];
    }

    /**
     * Method tests deleteFiltered method
     *
     * @param mixed $domainId
     *            Domain id
     *            
     * @dataProvider deleteFilteredTestData
     */
    public function testDeleteFiltered($domainId)
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())
            ->method('delete')
            ->willReturn(1);
        $mock = $this->getModelMock($connection);

        // test body and assertions
        $mock->deleteFiltered($domainId, [
            'title LIKE "title"'
        ]);
    }

    /**
     * Test data for testConstructor test
     *
     * @return array
     */
    public function constructorTestData(): array
    {
        return [
            [
                [
                    'id' => [
                        'type' => 'intger'
                    ]
                ],
                'id'
            ],
            [
                '*',
                '*'
            ],
            [
                new \Mezon\FieldsSet([
                    'id' => [
                        'type' => 'intger'
                    ]
                ]),
                'id'
            ]
        ];
    }

    /**
     * Testing constructor
     *
     * @param mixed $data
     *            Parameterfor constructor
     * @param string $origin
     *            original data for validation
     * @dataProvider constructorTestData
     */
    public function testConstructor($data, string $origin)
    {
        // setup and test body
        $model = new \Mezon\CrudService\CrudServiceModel($data, 'entity_name');

        // assertions
        $this->assertTrue($model->hasField($origin), 'Invalid contruction');
    }

    /**
     * Testing constructor with exception
     */
    public function testConstructorException()
    {
        $this->expectException(Exception::class);

        new \Mezon\CrudService\CrudServiceModel(new stdClass(), 'entity_name');
    }

    /**
     * Testing newRecordsSince
     */
    public function testNewRecordsSince()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);

        $model = $this->getModelMock($connection);

        // test body
        $records = $model->newRecordsSince(false, '2012-01-01');

        // assertions
        $this->assertEquals(2, count($records), 'Invalid count of new records');
    }

    /**
     * Testing getSimpleRecords without domain
     */
    public function testGetSimpleRecordsWithoutDomain()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);

        $model = $this->getModelMock($connection);

        // test body
        $records = $model->getSimpleRecords(false, 0, 2, [], [
            'field' => 'id',
            'order' => 'ASC'
        ]);

        // assertions
        $this->assertEquals(2, count($records), 'Invalid count of not transformed records');
    }

    /**
     * Testing getSimpleRecords with domain
     */
    public function testGetSimpleRecordsWithDomain()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);

        $model = $this->getModelMock($connection);

        // test body
        $records = $model->getSimpleRecords(1, 0, 2, [], [
            'field' => 'id',
            'order' => 'ASC'
        ]);

        // assertions
        $this->assertEquals(2, count($records), 'Invalid count of not transformed records');
    }

    /**
     * Testing fetchRecordsByIds with domain
     */
    public function testFetchRecordsByIdsWithDomain()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);

        $model = $this->getModelMock($connection);

        // test body
        $records = $model->fetchRecordsByIds(1, "1,2");

        // assertions
        $this->assertEquals(2, count($records), 'Invalid count of fetched by ids records');
    }

    /**
     * Testing fetchRecordsByIds with domain
     */
    public function testFetchRecordsByIdsWithoutDomain()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);

        $model = $this->getModelMock($connection);

        // test body
        $records = $model->fetchRecordsByIds(false, "1,2");

        // assertions
        $this->assertEquals(2, count($records), 'Invalid count of fetched by ids records');
    }

    /**
     * Testing fetchRecordsByIds not found
     */
    public function testFetchRecordsByIdsNotFound()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([]);

        $model = $this->getModelMock($connection);

        // test body and assertions
        $this->expectException(Exception::class);

        $model->fetchRecordsByIds("1,2", false);
    }

    /**
     * Data provider
     *
     * @return array Test data
     */
    public function recordsCountByFieldProvider(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'records_count' => 2
                ],
                2
            ],
            [
                [],
                0
            ]
        ];
    }

    /**
     * Testing recordsCountByField method
     *
     * @dataProvider recordsCountByFieldProvider
     */
    public function testRecordsCountByField(array $selectResult, int $count)
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn($selectResult);

        $model = $this->getModelMock($connection);

        // test body
        $result = $model->recordsCountByField(false, 'id', []);

        // assertions
        $this->assertEquals($count, $result['records_count'], 'Invalid records count was fetched');
    }

    /**
     * Method tests last N records returning
     */
    public function testLastRecords()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [],
            []
        ]);
        $mock = $this->getModelMock($connection);
        $mock->expects($this->once())
            ->method('getRecordsTransformer');

        // test body
        $records = $mock->lastRecords(false, 2, [
            '1 = 1'
        ]);

        // assertions
        $this->assertEquals(2, count($records), 'Invalid amount of records was returned');
    }

    /**
     * Testing getRecords method
     */
    public function testGetRecords()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->method('select')->willReturn([
            [
                'id' => 1
            ]
        ]);

        $mock = $this->getModelMock($connection);
        $mock->expects($this->once())
            ->method('getRecordsTransformer');

        // test body
        $result = $mock->getRecords(0, 0, 1);

        // assertions
        $this->assertCount(1, $result);
    }

    /**
     * Method tests updateBasicFields method
     */
    public function testUpdateBasicFields()
    {
        // setup
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())
            ->method('update');
        $mock = $this->getModelMock($connection);

        // test body and assertions
        $mock->updateBasicFields(false, [
            'id' => 1
        ], [
            '1=1'
        ]);
    }
}

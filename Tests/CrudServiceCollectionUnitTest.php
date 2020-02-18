<?php

class CrudServiceCollectionUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Testing constructor
     */
    public function testConstructorValid()
    {
        $collection = new \Mezon\CrudService\CrudServiceCollection('http://auth', 'some token');

        $this->assertInstanceOf(
            \Mezon\CrudService\CrudServiceClient::class,
            $collection->getConnector(),
            'Connector was not setup');
    }

    /**
     * Method returns mock connector
     */
    protected function getConnector()
    {
        $mock = $this->getMockBuilder(\Mezon\CrudService\CrudServiceClient::class)
            ->setMethods([
            'newRecordsSince',
            'getList'
        ])
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Testing newRecordsSince method
     */
    public function testNewRecordsSince()
    {
        // setupp
        $connector = $this->getConnector();
        $connector->method('newRecordsSince')->willReturn([
            [],
            []
        ]);

        $collection = new \Mezon\CrudService\CrudServiceCollection();
        $collection->setConnector($connector);

        // test body
        $collection->newRecordsSince('2019-01-01');

        // assertions
        $this->assertEquals(2, count($collection->getCollection()), 'Invalid records count');
    }

    /**
     * Testing top_by_field method
     */
    public function testTopByField()
    {
        // setupp
        $connector = $this->getConnector();
        $connector->method('getList')->willReturn([
            [],
            []
        ]);

        $collection = new \Mezon\CrudService\CrudServiceCollection();
        $collection->setConnector($connector);

        // test body
        $collection->topByField(2, 'id', 'DESC');

        // assertions
        $this->assertEquals(2, count($collection->getCollection()), 'Invalid records count');
    }
}

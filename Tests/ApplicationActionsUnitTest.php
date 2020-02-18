<?php

/**
 * Test application
 *
 * @author Dodonov A.A.
 */
class TestExtendingApplication extends \Mezon\Application\CommonApplication
{

    public function __construct()
    {
        parent::__construct(new \Mezon\Application\HtmlTemplate(__DIR__));
    }

    public function redirectTo($uRL): void
    {}
}

class TestApplicationActions extends \Mezon\CrudService\ApplicationActions
{

    public function getSelfId(): string
    {
        return 1;
    }
}

class ApplicationActionsUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Creating mock of the application actions
     *
     * @return object Application actions
     */
    protected function getApplicationActions(): object
    {
        $object = new TestApplicationActions('entity');

        $crudServiceClient = $this->getMockBuilder(\Mezon\CrudService\CrudServiceClient::class)
            ->setMethods([
            'getList',
            'delete',
            'getRemoteCreationFormFields'
        ])
            ->disableOriginalConstructor()
            ->getMock();

        $crudServiceClient->method('getList')->willReturn([
            [
                'id' => 1
            ]
        ]);

        $crudServiceClient->method('delete')->willReturn('');

        $crudServiceClient->method('getRemoteCreationFormFields')->willReturn(
            [
                'fields' => [
                    'id' => [
                        'type' => 'integer',
                        'title' => 'id'
                    ]
                ],
                'layout' => []
            ]);

        $object->setServiceClient($crudServiceClient);

        return $object;
    }
    
    /**
     * Common setup for all tests
     */
    public function setUp(): void
    {
        \Mezon\DnsClient\DnsClient::clear();
        \Mezon\DnsClient\DnsClient::setService('entity', 'http://entity.local/');
    }

    /**
     * Testing attaching list method
     */
    public function testAttachListPageMthodInvalid(): void
    {
        // setup
        $object = $this->getApplicationActions();

        $application = new TestExtendingApplication();

        // test body and assertions
        $this->expectException(Exception::class);

        $object->attachListPage($application, []);
        $application->entityListingPage();

    }

    /**
     * Testing attaching list method
     */
    public function testAttachListPageMethod(): void
    {
        // setup
        $object = $this->getApplicationActions();

        $application = new TestExtendingApplication();

        // test body
        $object->attachListPage($application, [
            'default-fields' => 'id'
        ]);

        $result = $application->entityListingPage();

        // assertions
        $this->assertTrue(isset($application->entityListingPage), 'Method "entityListingPage" does not exist');
        $this->assertStringContainsString('>1<', $result['main']);
        $this->assertStringContainsString('>id<', $result['main']);
    }

    /**
     * Testing attaching simple list method
     */
    public function testAttachSimpleListPageMethod(): void
    {
        // setup
        $object = $this->getApplicationActions();
        $application = new TestExtendingApplication();

        // test body
        $object->attachSimpleListPage($application, [
            'default-fields' => 'id'
        ]);
        $application->entitySimpleListingPage();

        // assertions
        $this->assertTrue(
            isset($application->entitySimpleListingPage),
            'Method "entitySimpleListingPage" does not exist');
    }

    /**
     * Testing attaching delete method
     */
    public function testAttachDeleteMethod(): void
    {
        // setup
        $object = $this->getApplicationActions();
        $application = new TestExtendingApplication();

        // test body
        $object->attachDeleteRecord($application, []);

        $application->entityDeleteRecord('/route/', [
            'id' => 1
        ]);

        // assertions
        $this->assertTrue(isset($application->entityDeleteRecord), 'Method "entityDeleteRecord" does not exist');
    }

    /**
     * Testing attaching create method
     */
    public function testAttachCreateMethod(): void
    {
        // setup
        $object = $this->getApplicationActions();
        $application = new TestExtendingApplication();

        // test body
        $object->attachCreateRecord($application, []);
        $result = $application->entityCreateRecord();

        // assertions
        $this->assertStringContainsString('x_title', $result['main'], 'Method "entityCreateRecord" does not exist');
    }

    /**
     * Testing attaching update method
     */
    public function testAttachUpdateMethod(): void
    {
        // setup
        $object = $this->getApplicationActions();
        $application = new TestExtendingApplication();

        // test body
        $object->attachUpdateRecord($application, []);

        // assertions
        $this->assertTrue(isset($application->entityUpdateRecord), 'Method "entityUpdateRecord" does not exist');
    }
}

<?php
namespace Mezon\CrudService\Tests;

/**
 * Class CrudServiceUnitTests
 *
 * @package CrudService
 * @subpackage CrudServiceUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */
define('GET_STRING', 1);
define('GET_OBJECT', 2);

/**
 * Fake security provider
 */
class FakeSecurityProviderForCrudService
{
}

class CrudServiceExceptionConstructorMock extends \Mezon\CrudService\CrudService
{

    public function __construct(\Mezon\Service\ServiceTransport $transport)
    {
        parent::__construct([
            'fields' => '*',
            'table-name' => 'table',
            'entity-name' => 'entity',
        ], $transport);
    }

    protected function initCommonRoutes(): void
    {
        throw (new \Exception('Testing exception'));
    }
}

/**
 * Basic service's unit tests
 *
 * @group baseTests
 */
class CrudServiceUnitTests extends \PHPUnit\Framework\TestCase
{

    /**
     * Service class name
     *
     * @var string
     */
    protected $className = \Mezon\CrudService\CrudService::class;

    /**
     * Method returns service settings
     *
     * @return array settings
     */
    protected function getServiceSettings(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/conf/SetupCrudServiceUnitTests.json'), true);
    }

    /**
     * Method checks route and method bindings
     *
     * @param string $route
     *            - Route to be checked
     * @param string $method
     *            - Method to be bound with route
     * @param string $requestMethod
     *            - HTTP request method
     */
    protected function checkRoute(string $route, string $method, string $requestMethod = 'GET')
    {
        $_GET['r'] = $route;

        $mock = $this->getMockBuilder(\Mezon\CrudService\CrudServiceLogic::class)
            ->setConstructorArgs(
            [
                (new \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport())->getParamsFetcher(),
                new FakeSecurityProviderForCrudService(),
                new \Mezon\CrudService\CrudServiceModel()
            ])
            ->setMethods([
            $method
        ])
            ->getMock();

        $mock->expects($this->once())
            ->method($method);

        $service = new $this->className(
            $this->getServiceSettings(),
            \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport::class,
            \Mezon\Service\ServiceMockSecurityProvider::class,
            $mock);

        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        $service->run();

        $this->addToAssertionCount(1);
    }

    /**
     * Method returns transport
     *
     * @param string $type
     *            - Type of return value
     * @return string Transport
     */
    protected function getTransport(string $type = GET_STRING)
    {
        if ($type == GET_STRING) {
            return \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport::class;
        } else {
            return new \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport();
        }
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructor()
    {
        $service = new \Mezon\CrudService\CrudService($this->getServiceSettings(), $this->getTransport());

        $this->assertInstanceOf(
            \Mezon\Service\ServiceMockSecurityProvider::class,
            $service->getTransport()->securityProvider);
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructorWithSecurityProviderString()
    {
        $service = new \Mezon\CrudService\CrudService(
            $this->getServiceSettings(),
            $this->getTransport(),
            FakeSecurityProviderForCrudService::class);

        $this->assertInstanceOf(FakeSecurityProviderForCrudService::class, $service->getTransport()->securityProvider);
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructorWithSecurityProviderObject()
    {
        // setup and test body
        $service = new \Mezon\CrudService\CrudService(
            $this->getServiceSettings(),
            $this->getTransport(),
            new FakeSecurityProviderForCrudService());

        // assertions
        $this->assertInstanceOf(FakeSecurityProviderForCrudService::class, $service->getTransport()->securityProvider);
    }

    /**
     * Testing CrudService constructor with exception
     */
    public function testServiceConstructorWithException()
    {
        // setup, test body and assertions
        $transport = $this->getMockBuilder(\Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport::class)
            ->setMethods([
            'handleException',
        ])
            ->getMock();

        $transport->expects($this->once())
            ->method('handleException');

        new CrudServiceExceptionConstructorMock($transport);
    }

    /**
     * Testing CrudService route processor
     *
     * @param string $route
     *            Route
     * @param string $handler
     *            Route handler
     * @param string $method
     *            GET|POST
     * @dataProvider routesDataProvider
     */
    public function testRoutes(string $route, string $handler, string $method)
    {
        // test body and assertions
        $this->checkRoute($route, $handler, $method);
    }

    /**
     * Data provider for the test testRoutes
     *
     * @return array
     * @codeCoverageIgnore
     */
    public static function routesDataProvider(): array
    {
        return [
            [
                '/list/',
                'listRecord',
                'GET',
            ],
            [
                '/all/',
                'all',
                'GET',
            ],
            [
                '/exact/list/[il:ids]/',
                'exactList',
                'GET',
            ],
            [
                '/exact/[i:id]/',
                'exact',
                'GET',
            ],
            [
                '/fields/',
                'fields',
                'GET',
            ],
            [
                '/delete/1/',
                'deleteRecord',
                'POST',
            ],
            [
                '/delete/',
                'deleteFiltered',
                'POST',
            ],
            [
                '/create/',
                'createRecord',
                'POST',
            ],
            [
                '/update/1/',
                'updateRecord',
                'POST',
            ],
            [
                '/new/from/2019-01-01/',
                'newRecordsSince',
                'GET',
            ],
            [
                '/records/count/',
                'recordsCount',
                'GET',
            ],
            [
                '/last/10/',
                'lastRecords',
                'GET',
            ],
            [
                '/records/count/id/',
                'recordsCountByField',
                'GET',
            ],
        ];
    }

    /**
     * Testing CrudService constructor
     */
    public function testMultipleModels()
    {
        // setup
        $model = new \Mezon\CrudService\CrudServiceModel();

        $transport = $this->getTransport(GET_OBJECT);

        $logic1 = new \Mezon\CrudService\CrudServiceLogic(
            $transport->paramsFetcher,
            new FakeSecurityProviderForCrudService(),
            $model);
        $logic2 = new \Mezon\CrudService\CrudServiceLogic(
            $transport->paramsFetcher,
            new FakeSecurityProviderForCrudService(),
            $model);

        // test body
        $service = new \Mezon\CrudService\CrudService(
            $this->getServiceSettings(),
            $this->getTransport(),
            new FakeSecurityProviderForCrudService(),
            [
                $logic1,
                $logic2
            ]);

        // assertions
        $this->assertInstanceOf(
            \Mezon\CrudService\CrudServiceModel::class,
            $service->getLogic()[0]->getModel(),
            'Logic was not stored properly');
        $this->assertInstanceOf(
            \Mezon\CrudService\CrudServiceModel::class,
            $service->getLogic()[1]->getModel(),
            'Logic was not stored properly');
    }
}

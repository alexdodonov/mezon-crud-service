<?php
namespace Mezon\CrudService\Tests;

use Mezon\CrudService\CrudServiceLogic;
use Mezon\CrudService\CrudServiceModel;
use Mezon\CrudService\CrudService;
use Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport;
use Mezon\Security\MockProvider;

/**
 * Class CrudServiceUnitTests
 *
 * @package CrudService
 * @subpackage CrudServiceUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Basic service's unit tests
 */
class CrudServiceUnitTests extends \PHPUnit\Framework\TestCase
{

    /**
     * Service class name
     *
     * @var string
     */
    protected $className = CrudService::class;

    /**
     * Method returns service settings
     *
     * @return array settings
     */
    protected function getServiceSettings(string $file = 'SetupCrudServiceUnitTests'): array
    {
        return json_decode(file_get_contents(__DIR__ . '/conf/' . $file . '.json'), true);
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

        $mock = $this->getMockBuilder(CrudServiceLogic::class)
            ->setConstructorArgs(
            [
                (new ServiceConsoleTransport())->getParamsFetcher(),
                new MockProvider(),
                new CrudServiceModel()
            ])
            ->setMethods([
            $method
        ])
            ->getMock();

        $mock->expects($this->once())
            ->method($method);

        $service = new $this->className(
            $this->getServiceSettings(),
            $mock,
            CrudServiceModel::class,
            MockProvider::class,
            ServiceConsoleTransport::class);

        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        $service->run();

        $this->addToAssertionCount(1);
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructor(): void
    {
        $service = new CrudService(
            $this->getServiceSettings(),
            CrudServiceLogic::class,
            CrudServiceModel::class,
            MockProvider::class,
            ServiceConsoleTransport::class);

        $this->assertInstanceOf(MockProvider::class, $service->getTransport()
            ->getSecurityProvider());
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructorWithSecurityProviderString(): void
    {
        $service = new CrudService(
            $this->getServiceSettings(),
            CrudServiceLogic::class,
            CrudServiceModel::class,
            MockProvider::class,
            ServiceConsoleTransport::class);

        $this->assertInstanceOf(
            MockProvider::class,
            $service->getTransport()
                ->getSecurityProvider());
    }

    /**
     * Testing CrudService constructor
     */
    public function testServiceConstructorWithSecurityProviderObject(): void
    {
        // setup and test body
        $service = new CrudService(
            $this->getServiceSettings(),
            CrudServiceLogic::class,
            CrudServiceModel::class,
            new MockProvider(),
            ServiceConsoleTransport::class);

        // assertions
        $this->assertInstanceOf(
            MockProvider::class,
            $service->getTransport()
                ->getSecurityProvider());
    }

    /**
     * Testing CrudService constructor with exception
     */
    public function testServiceConstructorWithException(): void
    {
        // setup, test body and assertions
        $transport = $this->getMockBuilder(ServiceConsoleTransport::class)
            ->setMethods([
            'handleException'
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
    public function testRoutes(string $route, string $handler, string $method): void
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
                'GET'
            ],
            [
                '/all/',
                'all',
                'GET'
            ],
            [
                '/exact/list/1,2/',
                'exactList',
                'GET'
            ],
            [
                '/exact/1/',
                'exact',
                'GET'
            ],
            [
                '/fields/',
                'fields',
                'GET'
            ],
            [
                '/delete/1/',
                'deleteRecord',
                'POST'
            ],
            [
                '/delete/',
                'deleteFiltered',
                'POST'
            ],
            [
                '/create/',
                'createRecord',
                'POST'
            ],
            [
                '/update/1/',
                'updateRecord',
                'POST'
            ],
            [
                '/new/from/2019-01-01/',
                'newRecordsSince',
                'GET'
            ],
            [
                '/records/count/',
                'recordsCount',
                'GET'
            ],
            [
                '/last/10/',
                'lastRecords',
                'GET'
            ],
            [
                '/records/count/id/',
                'recordsCountByField',
                'GET'
            ]
        ];
    }

    /**
     * Testing CrudService constructor
     */
    public function testMultipleModels(): void
    {
        // setup
        $model = new CrudServiceModel();

        $transport = new ServiceConsoleTransport();

        $logic1 = new CrudServiceLogic($transport->getParamsFetcher(), new MockProvider(), $model);
        $logic2 = new CrudServiceLogic($transport->getParamsFetcher(), new MockProvider(), $model);

        // test body
        $service = new CrudService($this->getServiceSettings(), [
            $logic1,
            $logic2
        ], CrudServiceModel::class, new MockProvider(), ServiceConsoleTransport::class);

        // assertions
        $this->assertInstanceOf(
            CrudServiceModel::class,
            $service->getLogic()[0]->getModel(),
            'Logic was not stored properly');
        $this->assertInstanceOf(
            CrudServiceModel::class,
            $service->getLogic()[1]->getModel(),
            'Logic was not stored properly');
    }

    /**
     * Testing fields loading from config
     */
    public function testGetFieldsFromConfig(): void
    {
        // setup and test body
        $service = new CrudServiceTest(
            $this->getServiceSettings('SetupCrudServiceNoFieldsUnitTests'),
            CrudServiceLogic::class,
            CrudServiceModel::class,
            new MockProvider(),
            ServiceConsoleTransport::class);

        // assertions
        $this->assertTrue($service->getLogic()[0]->getModel()
            ->hasField('id'));
    }
}

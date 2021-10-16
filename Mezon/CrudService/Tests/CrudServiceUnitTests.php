<?php
namespace Mezon\CrudService\Tests;

use Mezon\CrudService\CrudServiceLogic;
use Mezon\CrudService\CrudServiceModel;
use Mezon\CrudService\CrudService;
use Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport;
use Mezon\Security\MockProvider;
use PHPUnit\Framework\TestCase;
use Mezon\Service\ServiceModel;

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
class CrudServiceUnitTests extends TestCase
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

        $model = new CrudServiceModel();

        $transport = new ServiceConsoleTransport(new MockProvider());

        $serviceLogic = $this->getMockBuilder(CrudServiceLogic::class)
            ->setConstructorArgs([
            $transport->getParamsFetcher(),
            $transport->getSecurityProvider(),
            $model
        ])
            ->onlyMethods([
            $method
        ])
            ->getMock();

        $serviceLogic->expects($this->once())
            ->method($method);

        $transport->setServiceLogic($serviceLogic);

        $service = new $this->className($transport);

        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        $service->run();

        $this->addToAssertionCount(1);
    }

    /**
     * Testing CrudService constructor with exception
     */
    public function testServiceConstructorWithException(): void
    {
        // setup, test body and assertions
        $transport = $this->getMockBuilder(ServiceConsoleTransport::class)
            ->setConstructorArgs([
            new MockProvider()
        ])
            ->onlyMethods([
            'handleException'
        ])
            ->getMock();

        $transport->expects($this->once())
            ->method('handleException');

        $serviceLogic = new CrudServiceLogic(
            $transport->getParamsFetcher(),
            $transport->getSecurityProvider(),
            new ServiceModel());

        $transport->setServiceLogic($serviceLogic);

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

        // TODO create PublicAccessProvider it will be more semantic than MockProvider
        $provider = new MockProvider();

        $transport = new ServiceConsoleTransport($provider);

        $logic1 = new CrudServiceLogic($transport->getParamsFetcher(), $transport->getSecurityProvider(), $model);
        $logic2 = new CrudServiceLogic($transport->getParamsFetcher(), $transport->getSecurityProvider(), $model);

        $transport->setServiceLogics([
            $logic1,
            $logic2
        ]);

        // test body
        $service = new CrudService($transport);

        // assertions
        $this->assertInstanceOf(CrudServiceModel::class, $service->getTransport()->getServiceLogics()[0]->getModel());
        $this->assertInstanceOf(CrudServiceModel::class, $service->getTransport()->getServiceLogics()[1]->getModel());
    }

    /**
     * Testing fields loading from config
     */
    public function testGetFieldsFromConfig(): void
    {
        // setup and test body
        $provider = new MockProvider();
        $model = new CrudServiceModel([
            'id' => [
                'type' => 'string',
                'title' => 'All fields'
            ]
        ]);

        $transport = new ServiceConsoleTransport($provider);

        $serviceLogic = new CrudServiceLogic($transport->getParamsFetcher(), $transport->getSecurityProvider(), $model);

        $transport->setServiceLogic($serviceLogic);

        $service = new CrudServiceTest($transport);

        // assertions
        $this->assertTrue($service->getTransport()->getServiceLogics()[0]->getModel()
            ->hasField('id'));
    }
}

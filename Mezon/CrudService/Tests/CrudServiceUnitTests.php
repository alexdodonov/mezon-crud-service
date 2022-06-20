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
 * @copyright Copyright (c) 2019, http://aeon.su
 */

/**
 * Basic service's unit tests
 *
 * @psalm-suppress PropertyNotSetInConstructor
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
     *            route to be checked
     * @param string $method
     *            method to be bound with route
     * @param string $requestMethod
     *            HTTP request method
     */
    protected function checkRoute(string $route, string $method, string $requestMethod = 'GET'): void
    {
        $_GET['r'] = $route;

        $model = new CrudServiceModel();

        $transport = new ServiceConsoleTransport();

        $serviceLogic = $this->getMockBuilder(CrudServiceLogic::class)
            ->setConstructorArgs([
            $transport->getParamsFetcher(),
            new MockProvider(),
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

        $this->assertTrue(true);
    }

    /**
     * Testing CrudService constructor with exception
     */
    public function testServiceConstructorWithException(): void
    {
        // setup, test body and assertions
        $provider = new MockProvider();
        $transport = $this->getMockBuilder(ServiceConsoleTransport::class)
            ->setConstructorArgs([
            $provider
        ])
            ->onlyMethods([
            'handleException'
        ])
            ->getMock();

        $transport->expects($this->once())
            ->method('handleException');

        $serviceLogic = new CrudServiceLogic($transport->getParamsFetcher(), $provider, new ServiceModel());

        $transport->setServiceLogic($serviceLogic);

        new CrudServiceExceptionConstructorMock($transport);
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
}

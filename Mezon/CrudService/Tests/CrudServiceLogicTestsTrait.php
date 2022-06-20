<?php
namespace Mezon\CrudService\Tests;

use Mezon\CrudService\CrudServiceModel;
use Mezon\CrudService\CrudServiceLogic;
use Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport;
use Mezon\Security\MockProvider;
use Mezon\PdoCrud\PdoCrud;
use Mezon\PdoCrud\Tests\PdoCrudMock;
use Mezon\Service\ServiceHttpTransport\ServiceHttpTransport;

/**
 * Common methods for CrudServiceLogicTests
 *
 * @author Dodonov A.A.
 */
trait CrudServiceLogicTestsTrait
{

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
            'hasField',
            'getFields',
            'fetchRecordsByIds'
        ])
    {
        // TODO remove one usage
        return $this->getMockBuilder(CrudServiceModel::class)
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
            ->onlyMethods($methods)
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
        return json_decode(file_get_contents(__DIR__ . '/Conf/' . $fileName . '.json'), true);
    }

    /**
     * Method creates full functional CrudServiceLogic object
     *
     * @param mixed $model
     *            List of models or single model
     * @return CrudServiceLogic object
     */
    protected function getServiceLogic($model): CrudServiceLogic
    {
        // TODO remove one usage
        $transport = new ServiceConsoleTransport();

        return new CrudServiceLogic($transport->getParamsFetcher(), new MockProvider(), $model);
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
        // TODO remove one usage
        $transport = new ServiceConsoleTransport();

        return $this->getMockBuilder(CrudServiceLogic::class)
            ->setConstructorArgs([
            $transport->getParamsFetcher(),
            new MockProvider(),
            $model
        ])
            ->onlyMethods([
            'getSelfIdValue',
            'hasPermit'
        ])
            ->getMock();
    }

    /**
     * Method creates service logic for list methods testing
     *
     * @param
     *            CrudServiceLogic service logic object
     */
    protected function setupLogicForListMethodsTesting(): CrudServiceLogic
    {
        // TODO replace with PdoCrudMock
        $connection = $this->getMockBuilder(PdoCrud::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
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
     * Constructing logic
     *
     * @param CrudServiceModel $model
     *            model
     * @return CrudServiceLogic crud service logic
     */
    public function getServiceLogicForModel(CrudServiceModel $model): CrudServiceLogic
    {
        $serviceTransport = new ServiceHttpTransport();

        return new CrudServiceLogic($serviceTransport->getParamsFetcher(), new MockProvider(), $model);
    }

    /**
     * Constructing logic
     *
     * @param PdoCrudMock $connection
     *            connection mock
     * @return CrudServiceLogic crud service logic
     */
    public function getServiceLogicForConnection(PdoCrudMock $connection): CrudServiceLogic
    {
        $model = new CrudServiceModel();
        $model->setConnection($connection);

        return $this->getServiceLogicForModel($model);
    }

    /**
     * Method returns selecting pdo mock
     *
     * @param array $return
     *            return data
     * @return PdoCrudMock pdo mock
     */
    public function getSelectingConnection(array $return): PdoCrudMock
    {
        $connection = new PdoCrudMock();
        $connection->selectResults[] = $return;
        return $connection;
    }
}

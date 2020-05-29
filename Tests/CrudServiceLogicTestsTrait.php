<?php
namespace Mezon\CrudService\Tests;

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
            'setFieldForObject',
            'hasField',
            'getFields',
            'fetchRecordsByIds'
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
            $transport->getParamsFetcher(),
            new \Mezon\Security\MockProvider(),
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
                $transport->getParamsFetcher(),
                new \Mezon\Security\MockProvider(),
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
}
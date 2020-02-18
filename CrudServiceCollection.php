<?php
namespace Mezon\CrudService;

/**
 * Class CrudServiceCollection
 *
 * @package CrudService
 * @subpackage CrudServiceCollection
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Collection of the crud service's records
 *
 * @author Dodonov A.A.
 */
class CrudServiceCollection
{

    /**
     * Collection of records
     *
     * @var array
     */
    protected $сollection = [];

    /**
     * Connection to the Crud service
     *
     * @var \Mezon\CrudService\CrudServiceClientInterface
     */
    protected $сonnector = null;

    /**
     * Service name or URL
     *
     * @var string
     */
    protected $service = '';

    /**
     * Access token
     *
     * @var string
     */
    protected $token = '';

    /**
     * Constructor
     *
     * @param string $service
     * @param string $token
     */
    public function __construct(string $service = '', string $token = '')
    {
        if ($service !== '') {
            $this->service = $service;
            $this->token = $token;
        }
    }

    /**
     * Method constructs connector
     *
     * @return \Mezon\CrudService\CrudServiceClient Connector to the service
     */
    protected function constructClient(): \Mezon\CrudService\CrudServiceClient
    {
        $client = new \Mezon\CrudService\CrudServiceClient($this->service);

        $client->setToken($this->token);

        return $client;
    }

    /**
     * Method sets new connector
     *
     * @param \Mezon\CrudService\CrudServiceClientInterface $newConnector
     *            New connector
     */
    public function setConnector(\Mezon\CrudService\CrudServiceClientInterface $newConnector): void
    {
        $this->сonnector = $newConnector;
    }

    /**
     * Method returns connector to service
     *
     * @return \Mezon\CrudService\CrudServiceClientInterface
     */
    public function getConnector(): \Mezon\CrudService\CrudServiceClientInterface
    {
        if ($this->сonnector == null) {
            $this->сonnector = $this->constructClient();
        }

        return $this->сonnector;
    }

    /**
     * Method fetches scripts, wich were created since $dateTime
     *
     * @param string $dateTime
     */
    public function newRecordsSince(string $dateTime): void
    {
        $this->сollection = $this->getConnector()->newRecordsSince($dateTime);
    }

    /**
     * Fetching top $count records sorted by field
     *
     * @param int $count
     *            Count of records to be fetched
     * @param string $field
     *            Sorting field
     * @param string $order
     *            Sorting order
     */
    public function topByField(int $count, string $field, string $order = 'DESC'): void
    {
        $this->сollection = $this->getConnector()->getList(0, $count, 0, false, [
            'field' => $field,
            'order' => $order
        ]);
    }

    /**
     * Method returns previosly fetched collection
     *
     * @return array previosly fetched collection
     */
    public function getCollection(): array
    {
        return $this->сollection;
    }
}

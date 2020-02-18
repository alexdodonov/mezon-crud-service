<?php

/**
 * Class CrudServiceClientTests
 *
 * @package     CrudServiceClient
 * @subpackage  CrudServiceClientTests
 * @author      Dodonov A.A.
 * @version     v.1.0 (2019/08/17)
 * @copyright   Copyright (c) 2019, aeon.org
 */

/**
 * Common unit tests for CrudServiceClient and all derived client classes
 *
 * @author Dodonov A.A.
 * @group baseTests
 */
class CrudServiceClientTests extends \Mezon\Service\Tests\ServiceClientTests
{

    /**
     * Client class name
     */
    protected $clientClassName = '';

    /**
     * Method creates client object
     *
     * @param string $password
     */
    protected function constructClient(string $password = 'root')
    {
        return new $this->clientClassName(EXISTING_LOGIN, $password);
    }

    /**
     * Testing API connection
     */
    public function testValidConnect()
    {
        $client = $this->constructClient();

        $this->assertNotEquals($client->getSessionId(), false, 'Connection failed');
        $this->assertEquals($client->Login, EXISTING_LOGIN, 'Login was not saved');
    }

    /**
     * Testing invalid API connection
     */
    public function testInValidConnect()
    {
        $this->expectException(Exception::class);

        $this->constructClient('1234567');
    }

    /**
     * Testing setting valid token
     */
    public function testSetValidToken()
    {
        $client = $this->constructClient();

        $newClient = new $this->clientClassName();
        $newClient->setToken($client->getSessionId());

        $this->assertNotEquals($newClient->getSessionId(), false, 'Token was not set(1)');
    }

    /**
     * Testing setting valid token and login
     */
    public function testSetValidTokenAndLogin()
    {
        $client = $this->constructClient();

        $newClient = new $this->clientClassName();
        $newClient->setToken($client->getSessionId(), 'alexey@dodonov.none');

        $this->assertNotEquals($newClient->getSessionId(), false, 'Token was not set(2)');
        $this->assertNotEquals($newClient->getStoredLogin(), false, 'Login was not saved');
    }

    /**
     * Testing setting invalid token
     */
    public function testSetInValidToken()
    {
        $client = new $this->clientClassName();

        $this->expectException(Exception::class);

        $client->setToken('unexistingtoken');
    }

    /**
     * Testing loginAs method
     */
    public function testLoginAs()
    {
        $client = $this->construct_client();

        try {
            $client->loginAs(EXISTING_LOGIN);
        } catch (Exception $e) {
            $this->assertEquals(0, 1, 'Login was was not called properly');
        }
    }

    /**
     * Testing loginAs method with failed call
     */
    public function testFailedLoginAs()
    {
        $client = $this->construct_client();

        $this->expectException(Exception::class);

        $client->loginAs('alexey@dodonov.none');
    }

    /**
     * Testing situation that loginAs will not be called after the connect() call with the same login
     */
    public function testSingleLoginAs()
    {
        $this->assertEquals(0, 1, 'Test was not created');
    }
}

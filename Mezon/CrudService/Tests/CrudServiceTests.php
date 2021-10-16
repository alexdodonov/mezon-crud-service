<?php
namespace Mezon\CrudService\Tests;

use Mezon\Service\Tests\ServiceTests;

/**
 * Class CrudServiceTests
 *
 * @package CrudService
 * @subpackage CrudServiceTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Predefined set of tests for crud service.
 *
 * @author Dodonov A.A.
 * @group baseTests
 */
abstract class CrudServiceTests extends ServiceTests
{

    /**
     * Method tests list endpoint.
     */
    public function testList()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/list/?from=0&limit=20';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals(count($result) > 0, true, 'No records were returned');
    }

    /**
     * Method tests cross domain list endpoint.
     */
    public function testCrossDomainList()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/list/?from=0&limit=20&cross_domain=1';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals(count($result) > 0, true, 'No records were listed');
    }

    /**
     * Method tests non cross domain list endpoint.
     */
    public function testNonCrossDomainList()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/list/?from=0&limit=20&cross_domain=0';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals(count($result) > 0, true, 'No records were listed');
    }

    /**
     * Method tests records counter.
     */
    public function testRecordsCount()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/records/count/';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals($result > 0, true, 'Invalid records counting (>0)');
    }

    /**
     * Method tests list page endpoint.
     */
    public function testListPage()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/list/page/';

        $result = $this->getHtmlRequest($uRL);

        $this->assertTrue(isset($result->main), 'Page view was not generated');
    }

    /**
     * Method tests last records fetching.
     */
    public function testLastRecords2()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/last/2/';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals(count($result) > 0, true, 'Invalid records counting (2)');
    }

    /**
     * Method tests last records fetching.
     */
    public function testLastRecords0()
    {
        $this->validConnect();

        $uRL = $this->ServerPath . '/last/0/';

        $result = $this->getHtmlRequest($uRL);

        $this->assertEquals(count($result), 0, 'Invalid records counting (0)');
    }
}

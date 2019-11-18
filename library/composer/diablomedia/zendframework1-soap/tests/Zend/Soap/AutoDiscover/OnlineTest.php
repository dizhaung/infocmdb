<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(__FILE__) . '/../_files/commontypes.php';

/**
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Soap
 */
class Zend_Soap_AutoDiscover_OnlineTest extends PHPUnit\Framework\TestCase
{
    protected $baseuri;

    public function setUp()
    {
        if (!defined('TESTS_ZEND_SOAP_AUTODISCOVER_ONLINE_SERVER_BASEURI') || constant('TESTS_ZEND_SOAP_AUTODISCOVER_ONLINE_SERVER_BASEURI') == false) {
            $this->markTestSkipped('The constant TESTS_ZEND_SOAP_AUTODISCOVER_ONLINE_SERVER_BASEURI has to be defined to allow the Online test to work.');
        }
        $this->baseuri = TESTS_ZEND_SOAP_AUTODISCOVER_ONLINE_SERVER_BASEURI;
    }

    public function testNestedObjectArrayResponse()
    {
        $wsdl = $this->baseuri . '/server1.php?wsdl';

        $b      = new Zend_Soap_Wsdl_ComplexTypeB();
        $b->bar = 'test';
        $b->foo = 'test';

        $client = new Zend_Soap_Client($wsdl);
        $ret    = $client->request($b);

        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);
        $this->assertInternalType('array', $ret[0]->baz);
        $this->assertCount(3, $ret[0]->baz);

        $baz = $ret[0]->baz;
        $this->assertEquals('bar', $baz[0]->bar);
        $this->assertEquals('bar', $baz[0]->foo);
        $this->assertEquals('foo', $baz[1]->bar);
        $this->assertEquals('foo', $baz[1]->foo);
        $this->assertEquals('test', $baz[2]->bar);
        $this->assertEquals('test', $baz[2]->foo);
    }

    public function testObjectResponse()
    {
        $wsdl = $this->baseuri . '/server2.php?wsdl';

        $client = new Zend_Soap_Client($wsdl);
        $ret    = $client->request('test', 'test');

        $this->assertTrue(($ret instanceof stdClass));
        $this->assertEquals('test', $ret->foo);
        $this->assertEquals('test', $ret->bar);
    }
}

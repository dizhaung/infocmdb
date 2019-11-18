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
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Log
 */
class Zend_Log_Writer_ZendMonitorTest extends PHPUnit\Framework\TestCase
{
    /**
     * @group ZF-10081
     * @doesNotPerformAssertions
     */
    public function testWrite()
    {
        $writer = new Zend_Log_Writer_ZendMonitor();
        $writer->write(array('message' => 'my mess', 'priority' => 1));
    }

    public function testFactory()
    {
        $cfg = array();

        $writer = Zend_Log_Writer_ZendMonitor::factory($cfg);
        $this->assertTrue($writer instanceof Zend_Log_Writer_ZendMonitor);
    }

    public function testIsEnabled()
    {
        $writer = new Zend_Log_Writer_ZendMonitor();
        $this->assertInternalType('bool', $writer->isEnabled());
    }
}

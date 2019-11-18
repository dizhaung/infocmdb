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
class Zend_Log_Writer_AbstractTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Zend_Log_Writer_Abstract
     */
    protected $_writer;

    protected function setUp()
    {
        $this->_writer = new Zend_Log_Writer_AbstractTest_Concrete();
    }

    /**
     * @group ZF-6085
     */
    public function testSetFormatter()
    {
        if (version_compare(phpversion(), '7', '>=')) {
            $this->markTestSkipped('Invalid typehinting is PHP Fatal error in PHP7+');
        }

        $this->_writer->setFormatter(new Zend_Log_Formatter_Simple());
        $this->expectException('PHPUnit\Framework\Error\Error');
        $this->_writer->setFormatter(new StdClass());
    }

    public function testAddFilter()
    {
        $this->_writer->addFilter(1);
        $this->_writer->addFilter(new Zend_Log_Filter_Message('/mess/'));
        $this->expectException('Zend_Log_Exception');
        $this->_writer->addFilter(new StdClass());
    }

    /**
     * @group ZF-8953
     */
    public function testFluentInterface()
    {
        $instance = $this->_writer->addFilter(1)
                                  ->setFormatter(new Zend_Log_Formatter_Simple());

        $this->assertTrue($instance instanceof Zend_Log_Writer_AbstractTest_Concrete);
    }
}

class Zend_Log_Writer_AbstractTest_Concrete extends Zend_Log_Writer_Abstract
{
    protected function _write($event)
    {
    }

    public static function factory($config)
    {
    }
}

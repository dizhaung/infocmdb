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
 * @package    Zend_Loader
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/*
 * Preload a number of classes to ensure they're available once we've disabled
 * other autoloaders.
 */
require_once __DIR__ . '/../../../src/Zend/Loader/Exception/InvalidArgumentException.php';
require_once __DIR__ . '/../../../src/Zend/Loader/AutoloaderFactory.php';
require_once __DIR__ . '/../../../src/Zend/Loader/ClassMapAutoloader.php';
require_once __DIR__ . '/../../../src/Zend/Loader/StandardAutoloader.php';
// Trigger autoloader for these
class_exists('PHPUnit\Framework\Constraint\IsNull');
class_exists('PHPUnit\Framework\Constraint\IsTrue');
class_exists('PHPUnit\Framework\Constraint\IsType');
class_exists('SebastianBergmann\Exporter\Exporter');
class_exists('PHPUnit\Framework\Constraint\IsEqual');
class_exists('PHPUnit\Framework\Constraint\Count');
class_exists('PHPUnit\Framework\Constraint\Exception');

/**
 * @package    Zend_Loader
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Loader
 */
class Zend_Loader_AutoloaderFactoryTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Clear out other autoloaders to ensure those being tested are at the
        // top of the stack
        foreach ($this->loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        // Store original include_path
        $this->includePath = get_include_path();
    }

    public function tearDown()
    {
        Zend_Loader_AutoloaderFactory::unregisterAutoloaders();
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testRegisteringValidMapFilePopulatesAutoloader()
    {
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_ClassMapAutoloader' => array(
                dirname(__FILE__) . '/_files/goodmap.php',
            ),
        ));
        $loader = Zend_Loader_AutoloaderFactory::getRegisteredAutoloader('Zend_Loader_ClassMapAutoloader');
        $map    = $loader->getAutoloadMap();
        $this->assertInternalType('array', $map);
        $this->assertCount(2, $map);
    }

    /**
     * This tests checks if invalid autoloaders cause exceptions
     *
     */
    public function testFactoryCatchesInvalidClasses()
    {
        $this->expectException(\Zend_Loader_Exception_InvalidArgumentException::class);

        if (!version_compare(PHP_VERSION, '5.3.7', '>=')) {
            $this->markTestSkipped('Cannot test invalid interface loader with versions less than 5.3.7');
        }
        include dirname(__FILE__) . '/_files/InvalidInterfaceAutoloader.php';
        Zend_Loader_AutoloaderFactory::factory(array(
            'InvalidInterfaceAutoloader' => array()
        ));
    }

    public function testFactoryDoesNotRegisterDuplicateAutoloaders()
    {
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_StandardAutoloader' => array(
                'prefixes' => array(
                    'TestPrefix' => dirname(__FILE__) . '/TestAsset/TestPrefix',
                ),
            ),
        ));
        $this->assertCount(1, Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders());
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_StandardAutoloader' => array(
                'prefixes' => array(
                    'ZendTest_Loader_TestAsset_TestPlugins' => dirname(__FILE__) . '/TestAsset/TestPlugins',
                ),
            ),
        ));
        $this->assertCount(1, Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders());
        $this->assertTrue(class_exists('TestPrefix_NoDuplicateAutoloadersCase'));
        $this->assertTrue(class_exists('ZendTest_Loader_TestAsset_TestPlugins_Foo'));
    }

    public function testCanUnregisterAutoloaders()
    {
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_StandardAutoloader' => array(
                'prefixes' => array(
                    'TestPrefix' => dirname(__FILE__) . '/TestAsset/TestPrefix',
                ),
            ),
        ));
        Zend_Loader_AutoloaderFactory::unregisterAutoloaders();
        $this->assertCount(0, Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testCanUnregisterAutoloadersByClassName()
    {
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_StandardAutoloader' => array(
                'namespaces' => array(
                    'TestPrefix' => dirname(__FILE__) . '/TestAsset/TestPrefix',
                ),
            ),
        ));
        Zend_Loader_AutoloaderFactory::unregisterAutoloader('Zend_Loader_StandardAutoloader');
        $this->assertCount(0, Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testCanGetValidRegisteredAutoloader()
    {
        Zend_Loader_AutoloaderFactory::factory(array(
            'Zend_Loader_StandardAutoloader' => array(
                'namespaces' => array(
                    'TestPrefix' => dirname(__FILE__) . '/TestAsset/TestPrefix',
                ),
            ),
        ));
        $autoloader = Zend_Loader_AutoloaderFactory::getRegisteredAutoloader('Zend_Loader_StandardAutoloader');
        $this->assertTrue($autoloader instanceof Zend_Loader_StandardAutoloader);
    }

    public function testDefaultAutoloader()
    {
        Zend_Loader_AutoloaderFactory::factory();
        $autoloader = Zend_Loader_AutoloaderFactory::getRegisteredAutoloader('Zend_Loader_StandardAutoloader');
        $this->assertTrue($autoloader instanceof Zend_Loader_StandardAutoloader);
        $this->assertCount(1, Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testGetInvalidAutoloaderThrowsException()
    {
        $this->expectException('Zend_Loader_Exception_InvalidArgumentException');
        $loader = Zend_Loader_AutoloaderFactory::getRegisteredAutoloader('InvalidAutoloader');
    }

    public function testFactoryWithInvalidArgumentThrowsException()
    {
        $this->expectException('Zend_Loader_Exception_InvalidArgumentException');
        Zend_Loader_AutoloaderFactory::factory('InvalidArgument');
    }

    public function testFactoryWithInvalidAutoloaderClassThrowsException()
    {
        $this->expectException('Zend_Loader_Exception_InvalidArgumentException');
        Zend_Loader_AutoloaderFactory::factory(array('InvalidAutoloader' => array()));
    }

    public function testCannotBeInstantiatedViaConstructor()
    {
        $reflection  = new ReflectionClass('Zend_Loader_AutoloaderFactory');
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor);
    }

    public function testPassingNoArgumentsToFactoryInstantiatesAndRegistersStandardAutoloader()
    {
        Zend_Loader_AutoloaderFactory::factory();
        $loaders = Zend_Loader_AutoloaderFactory::getRegisteredAutoloaders();
        $this->assertCount(1, $loaders);
        $loader = array_shift($loaders);
        $this->assertTrue($loader instanceof Zend_Loader_StandardAutoloader);

        $test  = array($loader, 'autoload');
        $found = false;
        foreach (spl_autoload_functions() as $function) {
            if ($function === $test) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'StandardAutoloader not registered with spl_autoload');
    }
}

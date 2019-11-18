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
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Test class for Zend_Dojo_Form and Zend_Dojo_Form_DisplayGroup
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_Form
 */
class Zend_Dojo_Form_FormTest extends PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->form = new Zend_Dojo_Form();
        $this->form->addElement('TextBox', 'foo')
                   ->addDisplayGroup(array('foo'), 'dg')
                   ->setView(new Zend_View());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function testDojoFormDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->getPluginLoader('decorator')->getPaths('Zend_Dojo_Form_Decorator');
        $this->assertInternalType('array', $paths);
    }

    public function testDojoFormElementPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->getPluginLoader('element')->getPaths('Zend_Dojo_Form_Element');
        $this->assertInternalType('array', $paths);
    }

    public function testDojoFormElementDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->foo->getPluginLoader('decorator')->getPaths('Zend_Dojo_Form_Decorator');
        $this->assertInternalType('array', $paths);
    }

    public function testDojoFormDisplayGroupDecoratorPathShouldBeRegisteredByDefault()
    {
        $paths = $this->form->dg->getPluginLoader()->getPaths('Zend_Dojo_Form_Decorator');
        $this->assertInternalType('array', $paths);
    }

    public function testDefaultDisplayGroupClassShouldBeDojoDisplayGroupByDefault()
    {
        $this->assertEquals('Zend_Dojo_Form_DisplayGroup', $this->form->getDefaultDisplayGroupClass());
    }

    public function testDefaultDecoratorsShouldIncludeDijitForm()
    {
        $this->assertNotNull($this->form->getDecorator('DijitForm'));
    }

    public function testShouldRegisterDojoViewHelperPath()
    {
        $view   = $this->form->getView();
        $loader = $view->getPluginLoader('helper');
        $paths  = $loader->getPaths('Zend_Dojo_View_Helper');
        $this->assertInternalType('array', $paths);
    }

    public function testDisplayGroupShouldRegisterDojoViewHelperPath()
    {
        $this->form->dg->setView(new Zend_View());
        $view   = $this->form->dg->getView();
        $loader = $view->getPluginLoader('helper');
        $paths  = $loader->getPaths('Zend_Dojo_View_Helper');
        $this->assertInternalType('array', $paths);
    }

    /**
     * @group ZF-4748
     */
    public function testHtmlTagDecoratorShouldHaveZendFormDojoClassByDefault()
    {
        $decorator = $this->form->getDecorator('HtmlTag');
        $this->assertEquals('zend_form_dojo', $decorator->getOption('class'));
    }
}

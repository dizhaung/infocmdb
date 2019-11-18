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
 * Test class for Zend_Dojo_Form_Element_TextBox.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_Form
 */
class Zend_Dojo_Form_Element_TextBoxTest extends PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();

        $this->view    = $this->getView();
        $this->element = $this->getElement();
        $this->element->setView($this->view);
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

    public function getView()
    {
        $view = new Zend_View();
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        return $view;
    }

    public function getElement()
    {
        $element = new Zend_Dojo_Form_Element_TextBox(
            'foo',
            array(
                'value'      => 'some text',
                'label'      => 'TextBox',
                'trim'       => true,
                'propercase' => true,
                'class'      => 'someclass',
                'style'      => 'width: 100px;',
            )
        );
        return $element;
    }

    public function testLowercaseAccessorsShouldProxyToDijitParams()
    {
        $this->assertFalse($this->element->getLowercase());
        $this->assertArrayNotHasKey('lowercase', $this->element->dijitParams);
        $this->element->setLowercase(true);
        $this->assertTrue($this->element->getLowercase());
        $this->assertTrue($this->element->dijitParams['lowercase']);
    }

    public function testPropercaseAccessorsShouldProxyToDijitParams()
    {
        $this->assertTrue($this->element->getPropercase());
        $this->assertArrayHasKey('propercase', $this->element->dijitParams);
        $this->element->setPropercase(false);
        $this->assertFalse($this->element->getPropercase());
    }

    public function testUppercaseAccessorsShouldProxyToDijitParams()
    {
        $this->assertFalse($this->element->getUppercase());
        $this->assertArrayNotHasKey('uppercase', $this->element->dijitParams);
        $this->element->setUppercase(true);
        $this->assertTrue($this->element->getUppercase());
        $this->assertTrue($this->element->dijitParams['uppercase']);
    }

    public function testTrimAccessorsShouldProxyToDijitParams()
    {
        $this->assertTrue($this->element->getTrim());
        $this->assertArrayHasKey('trim', $this->element->dijitParams);
        $this->element->setTrim(false);
        $this->assertFalse($this->element->getTrim());
    }

    public function testMaxLengthAccessorsShouldProxyToDijitParams()
    {
        $this->assertNull($this->element->getMaxLength());
        $this->assertArrayNotHasKey('maxLength', $this->element->dijitParams);
        $this->element->setMaxLength(20);
        $this->assertEquals(20, $this->element->getMaxLength());
        $this->assertEquals(20, $this->element->dijitParams['maxLength']);
    }
}

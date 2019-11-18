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
 * Test class for Zend_Dojo_Form_Element_ComboBox.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Dojo
 * @group      Zend_Dojo_Form
 */
class Zend_Dojo_Form_Element_ComboBoxTest extends PHPUnit\Framework\TestCase
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
        $element = new Zend_Dojo_Form_Element_ComboBox(
            'foo',
            array(
                'label' => 'ComboBox',
            )
        );
        return $element;
    }

    public function testSettingStoreIdShouldProxyToStoreDijitParam()
    {
        $this->element->setStoreId('someStore');
        $this->assertTrue($this->element->hasDijitParam('store'));
        $store = $this->element->getDijitParam('store');
        $this->assertArrayHasKey('store', $store);
        $this->assertEquals('someStore', $store['store']);
        $this->assertEquals($this->element->getStoreId(), $store['store']);
    }

    public function testSettingStoreTypeShouldProxyToStoreDijitParam()
    {
        $this->element->setStoreType('dojo.data.ItemFileReadStore');
        $this->assertTrue($this->element->hasDijitParam('store'));
        $store = $this->element->getDijitParam('store');
        $this->assertArrayHasKey('type', $store);
        $this->assertEquals('dojo.data.ItemFileReadStore', $store['type']);
        $this->assertEquals($this->element->getStoreType(), $store['type']);
    }

    public function testSettingStoreParamsShouldProxyToStoreDijitParam()
    {
        $this->element->setStoreParams(array('url' => '/js/foo.json'));
        $this->assertTrue($this->element->hasDijitParam('store'));
        $store = $this->element->getDijitParam('store');
        $this->assertArrayHasKey('params', $store);
        $this->assertEquals(array('url' => '/js/foo.json'), $store['params']);
        $this->assertEquals($this->element->getStoreParams(), $store['params']);
    }

    public function testAutocompleteAccessorsShouldProxyToDijitParams()
    {
        $this->assertFalse($this->element->getAutocomplete());
        $this->assertArrayNotHasKey('autocomplete', $this->element->dijitParams);
        $this->element->setAutocomplete(true);
        $this->assertTrue($this->element->getAutocomplete());
        $this->assertArrayHasKey('autocomplete', $this->element->dijitParams);
    }

    /**#@+
     * @group ZF-3286
     */
    public function testShouldNeverRegisterInArrayValidatorAutomatically()
    {
        $options = array(
            'foo' => 'Foo Value',
            'bar' => 'Bar Value',
            'baz' => 'Baz Value',
        );
        $this->element->setMultiOptions($options);
        $this->assertFalse($this->element->getValidator('InArray'));
        $this->element->isValid('test');
        $this->assertFalse($this->element->getValidator('InArray'));
    }
    /**#@-*/

    public function testShouldRenderComboBoxDijit()
    {
        $html = $this->element->render();
        $this->assertContains('dojoType="dijit.form.ComboBox"', $html);
    }

    /**
     * @group ZF-7134
     * @group ZF-7266
     */
    public function testComboBoxInSubFormShouldCreateJsonStoreBasedOnQualifiedId()
    {
        Zend_Dojo_View_Helper_Dojo::setUseProgrammatic();
        $this->element->setStoreId('foo')
                      ->setStoreType('dojo.data.ItemFileReadStore')
                      ->setStoreParams(array(
                          'url' => '/foo',
                        ));

        include_once 'Zend/Form/SubForm.php';
        $subform = new Zend_Form_SubForm(array('name' => 'bar'));
        $subform->addElement($this->element);
        $html = $this->element->render();
        $dojo = $this->view->dojo()->__toString();
        $this->assertContains('"store":"foo"', $dojo, $dojo);
    }
}

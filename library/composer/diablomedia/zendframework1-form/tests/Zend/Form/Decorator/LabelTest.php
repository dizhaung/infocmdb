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
 * @package    Zend_Form
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Test class for Zend_Form_Decorator_Label
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Form
 */
class Zend_Form_Decorator_LabelTest extends PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->decorator = new Zend_Form_Decorator_Label();
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
        $view->addHelperPath(dirname(__FILE__) . '/../../../../library/Zend/View/Helper');
        return $view;
    }

    public function testUsesPrependPlacementByDefault()
    {
        $this->assertEquals(Zend_Form_Decorator_Abstract::PREPEND, $this->decorator->getPlacement());
    }

    public function testRenderReturnsOriginalContentWhenNoViewPresentInElement()
    {
        $element = new Zend_Form_Element('foo');
        $this->decorator->setElement($element);
        $content = 'test content';
        $this->assertSame($content, $this->decorator->render($content));
    }

    public function testRenderReturnsOriginalContentWhenNoLabelPresentInElement()
    {
        $element = new Zend_Form_Element('foo');
        $this->decorator->setElement($element);
        $content = 'test content';
        $this->assertSame($content, $this->decorator->render($content));
    }

    public function testRenderUsesElementIdIfSet()
    {
        $element = new Zend_Form_Element('foo');
        $element->setAttrib('id', 'foobar')
                ->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertContains('for="foobar"', $test);
    }

    public function testRenderAddsOptionalClassForNonRequiredElements()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertRegExp('/<label[^>]*?class="[^"]*optional/', $test, $test);

        $element->class = 'bar';
        $this->decorator->setOption('class', 'foo');
        $test = $this->decorator->render($content);
        $this->assertNotRegExp('/<label[^>]*?class="[^"]*bar/', $test, $test);
        $this->assertRegExp('/<label[^>]*?class="[^"]*foo/', $test, $test);
        $this->assertRegExp('/<label[^>]*?class="[^"]*optional/', $test, $test);
    }

    public function testRenderAddsRequiredClassForRequiredElements()
    {
        $element = new Zend_Form_Element('foo');
        $element->setRequired(true)
                ->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertRegExp('/<label[^>]*?class="[^"]*required/', $test, $test);

        $element->class = 'bar';
        $this->decorator->setOption('class', 'foo');
        $test = $this->decorator->render($content);
        $this->assertNotRegExp('/<label[^>]*?class="[^"]*bar/', $test, $test);
        $this->assertRegExp('/<label[^>]*?class="[^"]*foo/', $test, $test);
        $this->assertRegExp('/<label[^>]*?class="[^"]*required/', $test, $test);
    }

    public function testRenderAppendsRequiredClassToClassProvidedInRequiredElement()
    {
        $element = new Zend_Form_Element('foo');
        $element->setRequired(true)
                ->setView($this->getView())
                ->setLabel('My Label')
                ->setAttrib('class', 'bazbat');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertRegExp('/<label[^>]*?class="[^"]*required/', $test, $test);
        $this->assertNotRegExp('/<label[^>]*?class="[^"]*bazbat/', $test, $test);
    }

    public function testRenderUtilizesOptionalSuffixesAndPrefixesWhenRequested()
    {
        $element = new Zend_Form_Element('foo');
        $element->setAttribs(array(
                    'optionalPrefix' => '-opt-prefix-',
                    'optionalSuffix' => '-opt-suffix-',
                    'requiredPrefix' => '-req-prefix-',
                    'requiredSuffix' => '-req-suffix-',
                  ))
                ->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertNotContains('-req-prefix-', $test, $test);
        $this->assertNotContains('-req-suffix-', $test, $test);
        $this->assertContains('-opt-prefix-', $test, $test);
        $this->assertContains('-opt-suffix-', $test, $test);
        $this->assertRegExp('/-opt-prefix-[^-]*?My Label[^-]*-opt-suffix-/s', $test, $test);
    }

    public function testRenderUtilizesRequiredSuffixesAndPrefixesWhenRequested()
    {
        $element = new Zend_Form_Element('foo');
        $element->setAttribs(array(
                    'optionalPrefix' => '-opt-prefix-',
                    'optionalSuffix' => '-opt-suffix-',
                    'requiredPrefix' => '-req-prefix-',
                    'requiredSuffix' => '-req-suffix-',
                  ))
                ->setRequired(true)
                ->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertNotContains('-opt-prefix-', $test, $test);
        $this->assertNotContains('-opt-suffix-', $test, $test);
        $this->assertContains('-req-prefix-', $test, $test);
        $this->assertContains('-req-suffix-', $test, $test);
        $this->assertRegExp('/-req-prefix-[^-]*?My Label[^-]*-req-suffix-/s', $test, $test);
    }

    /**
     * @group ZF-3538
     */
    public function testRenderShouldNotUtilizeElementClass()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label')
                ->setAttrib('class', 'foobar');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertNotRegExp('#<label[^>]*(class="[^"]*foobar)[^"]*"#', $test, $test);
    }

    public function testRenderRendersLabel()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element);
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertContains($content, $test);
        $this->assertContains($element->getLabel(), $test);
        $this->assertContains('<label for=', $test);
        $this->assertContains('</label>', $test);
    }

    public function testRenderAppendsOnRequest()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');
        $this->decorator->setElement($element)
                        ->setOptions(array('placement' => 'APPEND'));
        $content = 'test content';
        $test    = $this->decorator->render($content);
        $this->assertRegExp('#' . $content . '.*?<label#s', $test);
    }

    public function testCanChooseNotToEscapeLabel()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('<b>My Label</b>');
        $this->decorator->setElement($element)
                        ->setOptions(array('escape' => false));
        $test = $this->decorator->render('');
        $this->assertContains($element->getLabel(), $test);
    }

    public function testRetrievingLabelRetrievesLabelWithTranslationAndPrefixAndSuffix()
    {
        $translate = new Zend_Translate('array', array('My Label' => 'Translation'), 'en');
        $translate->setLocale('en');

        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label')
                ->setTranslator($translate);
        $this->decorator->setElement($element)
                        ->setOptions(array(
                            'optionalPrefix' => '> ',
                            'optionalSuffix' => ':',
                            'requiredPrefix' => '! ',
                            'requiredSuffix' => '*:',
                        ));
        $label = $this->decorator->getLabel();
        $this->assertEquals('> Translation:', $label);

        $element->setRequired(true);
        $label = $this->decorator->getLabel();
        $this->assertEquals('! Translation*:', $label);
    }

    public function testSettingTagToEmptyValueShouldDisableTag()
    {
        $element = new Zend_Form_Element_Text('foo', array('label' => 'Foo'));
        $this->decorator->setElement($element)
                        ->setTag('');
        $content = $this->decorator->render('');
        $this->assertEmpty($content, $content);
    }

    /**
     * @group ZF-4841
     */
    public function testSettingTagToEmptyValueShouldSetTagToNull()
    {
        $element = new Zend_Form_Element_Text('foo', array('label' => 'Foo'));
        $this->decorator->setElement($element)
                        ->setOptions(array('tag' => 'dt'));
        $this->decorator->setTag('');
        $tag = $this->decorator->getTag();
        $this->assertNull($tag);
    }


    /**
     * @group ZF-4841
     */
    public function testSettingTagClassToEmptyValueShouldSetTagClassToNull()
    {
        $element = new Zend_Form_Element_Text('foo', array('label' => 'Foo'));
        $this->decorator->setElement($element)
                        ->setOptions(array('tag' => 'dt'));
        $this->decorator->setTagClass('');
        $tagClass = $this->decorator->getTagClass();
        $this->assertNull($tagClass);
    }

    /**
     * @group ZF-6667
     */
    public function testRenderImplicitsOnRequest()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');

        $this->decorator->setElement($element)
                        ->setOptions(
                            array(
                                'placement' => 'IMPLICIT',
                                'separator' => ' ',
                            )
                        );

        $content = 'test content';
        $actual  = $this->decorator->render($content);

        $expected = '<label class="optional">My Label test content</label>';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @group ZF-6667
     */
    public function testRenderImplicitPrependsOnRequest()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');

        $this->decorator->setElement($element)
                        ->setOptions(
                            array(
                                'placement' => 'IMPLICIT_PREPEND',
                                'separator' => ' ',
                            )
                        );

        $content = 'test content';
        $actual  = $this->decorator->render($content);

        $expected = '<label class="optional">My Label test content</label>';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @group ZF-6667
     */
    public function testRenderImplicitAppendsOnRequest()
    {
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('My Label');

        $this->decorator->setElement($element)
                        ->setOptions(
                            array(
                                'placement' => 'IMPLICIT_APPEND',
                                'separator' => ' ',
                            )
                        );

        $content = 'test content';
        $actual  = $this->decorator->render($content);

        $expected = '<label class="optional">test content My Label</label>';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @group ZF-8694
     */
    public function testLabelIsNotTranslatedTwice()
    {
        // Init translator
        $translate = new Zend_Translate(
            array(
                 'adapter' => 'array',
                 'content' => array(
                     'firstLabel'  => 'secondLabel',
                     'secondLabel' => 'thirdLabel',
                 ),
                 'locale' => 'en'
            )
        );

        // Create element
        $element = new Zend_Form_Element('foo');
        $element->setView($this->getView())
                ->setLabel('firstLabel')
                ->setTranslator($translate);

        $this->decorator->setElement($element);

        // Test
        $this->assertEquals('secondLabel', $this->decorator->getLabel());
    }
}

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
 * @package    Zend_EventManager
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @category   Zend
 * @package    Zend_EventManager
 * @subpackage UnitTests
 * @group      Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_StaticEventManagerTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        Zend_EventManager_StaticEventManager::resetInstance();
    }

    public function tearDown()
    {
        Zend_EventManager_StaticEventManager::resetInstance();
    }

    public function testOperatesAsASingleton()
    {
        $expected = Zend_EventManager_StaticEventManager::getInstance();
        $test     = Zend_EventManager_StaticEventManager::getInstance();
        $this->assertSame($expected, $test);
    }

    public function testCanResetInstance()
    {
        $original = Zend_EventManager_StaticEventManager::getInstance();
        Zend_EventManager_StaticEventManager::resetInstance();
        $test = Zend_EventManager_StaticEventManager::getInstance();
        $this->assertNotSame($original, $test);
    }

    public function testSingletonInstanceIsInstanceOfClass()
    {
        $this->assertTrue(
            Zend_EventManager_StaticEventManager::getInstance() instanceof Zend_EventManager_StaticEventManager
        );
    }

    public function testCanAttachCallbackToEvent()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        $this->assertContains('bar', $events->getEvents('foo'));
        $expected  = array($this, __FUNCTION__);
        $found     = false;
        $listeners = $events->getListeners('foo', 'bar');
        $this->assertTrue($listeners instanceof Zend_Stdlib_PriorityQueue);
        $this->assertTrue(0 < count($listeners), 'Empty listeners!');
        foreach ($listeners as $listener) {
            if ($expected === $listener->getCallback()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Did not find listener!');
    }

    public function testCanAttachCallbackToMultipleEventsAtOnce()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('bar', array('foo', 'test'), array($this, __FUNCTION__));
        $this->assertContains('foo', $events->getEvents('bar'));
        $this->assertContains('test', $events->getEvents('bar'));
        $expected = array($this, __FUNCTION__);
        foreach (array('foo', 'test') as $event) {
            $found     = false;
            $listeners = $events->getListeners('bar', $event);
            $this->assertTrue($listeners instanceof Zend_Stdlib_PriorityQueue);
            $this->assertTrue(0 < count($listeners), 'Empty listeners!');
            foreach ($listeners as $listener) {
                if ($expected === $listener->getCallback()) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Did not find listener!');
        }
    }

    public function testCanAttachSameEventToMultipleResourcesAtOnce()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach(array('foo', 'test'), 'bar', array($this, __FUNCTION__));
        $this->assertContains('bar', $events->getEvents('foo'));
        $this->assertContains('bar', $events->getEvents('test'));
        $expected = array($this, __FUNCTION__);
        foreach (array('foo', 'test') as $id) {
            $found     = false;
            $listeners = $events->getListeners($id, 'bar');
            $this->assertTrue($listeners instanceof Zend_Stdlib_PriorityQueue);
            $this->assertTrue(0 < count($listeners), 'Empty listeners!');
            foreach ($listeners as $listener) {
                if ($expected === $listener->getCallback()) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Did not find listener!');
        }
    }

    public function testCanAttachCallbackToMultipleEventsOnMultipleResourcesAtOnce()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach(array('bar', 'baz'), array('foo', 'test'), array($this, __FUNCTION__));
        $this->assertContains('foo', $events->getEvents('bar'));
        $this->assertContains('test', $events->getEvents('bar'));
        $expected = array($this, __FUNCTION__);
        foreach (array('bar', 'baz') as $resource) {
            foreach (array('foo', 'test') as $event) {
                $found     = false;
                $listeners = $events->getListeners($resource, $event);
                $this->assertTrue($listeners instanceof Zend_Stdlib_PriorityQueue);
                $this->assertTrue(0 < count($listeners), 'Empty listeners!');
                foreach ($listeners as $listener) {
                    if ($expected === $listener->getCallback()) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found, 'Did not find listener!');
            }
        }
    }

    public function testListenersAttachedUsingWildcardEventWillBeTriggeredByResource()
    {
        $this->test         = new stdClass;
        $this->test->events = array();
        $callback           = array($this, 'setEventName');

        $staticEvents = Zend_EventManager_StaticEventManager::getInstance();
        $staticEvents->attach('bar', '*', $callback);

        $events = new Zend_EventManager_EventManager('bar');

        foreach (array('foo', 'bar', 'baz') as $event) {
            $events->trigger($event);
            $this->assertContains($event, $this->test->events);
        }
    }

    public function testCanDetachListenerFromResource()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        foreach ($events->getListeners('foo', 'bar') as $listener) {
            // only one; retrieving it so we can detach
        }
        $events->detach('foo', $listener);
        $listeners = $events->getListeners('foo', 'bar');
        $this->assertCount(0, $listeners);
    }

    public function testCanGetEventsByResource()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        $this->assertEquals(array('bar'), $events->getEvents('foo'));
    }

    public function testCanGetListenersByResourceAndEvent()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        $listeners = $events->getListeners('foo', 'bar');
        $this->assertTrue($listeners instanceof Zend_Stdlib_PriorityQueue);
        $this->assertCount(1, $listeners);
    }

    public function testCanClearListenersByResource()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        $events->attach('foo', 'baz', array($this, __FUNCTION__));
        $events->clearListeners('foo');
        $this->assertFalse($events->getListeners('foo', 'bar'));
        $this->assertFalse($events->getListeners('foo', 'baz'));
    }

    public function testCanClearListenersByResourceAndEvent()
    {
        $events = Zend_EventManager_StaticEventManager::getInstance();
        $events->attach('foo', 'bar', array($this, __FUNCTION__));
        $events->attach('foo', 'baz', array($this, __FUNCTION__));
        $events->attach('foo', 'bat', array($this, __FUNCTION__));
        $events->clearListeners('foo', 'baz');
        $this->assertTrue($events->getListeners('foo', 'baz') instanceof Zend_Stdlib_PriorityQueue);
        $this->assertCount(0, $events->getListeners('foo', 'baz'));
        $this->assertTrue($events->getListeners('foo', 'bar') instanceof Zend_Stdlib_PriorityQueue);
        $this->assertCount(1, $events->getListeners('foo', 'bar'));
        $this->assertTrue($events->getListeners('foo', 'bat') instanceof Zend_Stdlib_PriorityQueue);
        $this->assertCount(1, $events->getListeners('foo', 'bat'));
    }

    public function testCanPassArrayOfIdentifiersToConstructor()
    {
        $identifiers = array('foo', 'bar');
        $manager     = new Zend_EventManager_EventManager($identifiers);

        $this->assertTrue(true);
    }

    public function testListenersAttachedToAnyIdentifierProvidedToEventManagerWillBeTriggered()
    {
        $identifiers           = array('foo', 'bar');
        $manager               = new Zend_EventManager_EventManager($identifiers);
        $events                = Zend_EventManager_StaticEventManager::getInstance();
        $this->test            = new stdClass;
        $this->test->triggered = 0;
        $events->attach('foo', 'bar', array($this, 'advanceTriggered'));
        $events->attach('foo', 'bar', array($this, 'advanceTriggered'));
        $manager->trigger('bar', $this, array());
        $this->assertEquals(2, $this->test->triggered);
    }

    /*
     * Listeners used in tests
     */

    public function setEventName($e)
    {
        $this->test->events[] = $e->getName();
    }

    public function advanceTriggered($e)
    {
        $this->test->triggered++;
    }
}

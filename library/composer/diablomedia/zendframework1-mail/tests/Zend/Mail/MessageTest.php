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
 * @package    Zend_Mail
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Mail
 */
class Zend_Mail_MessageTest extends PHPUnit\Framework\TestCase
{
    protected $_file;

    public function setUp()
    {
        $this->_file = tempnam(sys_get_temp_dir(), 'zm_');
        $mail        = file_get_contents(dirname(__FILE__) . '/_files/mail.txt');
        $mail        = preg_replace("/(?<!\r)\n/", "\r\n", $mail);
        file_put_contents($this->_file, $mail);
    }

    public function tearDown()
    {
        if (file_exists($this->_file)) {
            unlink($this->_file);
        }
    }

    public function testInvalidFile()
    {
        $this->expectException(Exception::class);
        $message = new Zend_Mail_Message(array('file' => '/this/file/does/not/exists'));
    }

    public function testIsMultipart()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertTrue($message->isMultipart());
    }

    public function testGetHeader()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertEquals($message->subject, 'multipart');
    }

    public function testGetDecodedHeader()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $enc = PHP_VERSION_ID < 50600
            ? iconv_get_encoding('internal_encoding')
            : ini_get('default_charset');

        $this->assertEquals($message->from, iconv('UTF-8', $enc, '"Peter Müller" <peter-mueller@example.com>'));
    }

    public function testGetHeaderAsArray()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertEquals($message->getHeader('subject', 'array'), array('multipart'));
    }

    public function testGetHeaderFromOpenFile()
    {
        $fh      = fopen($this->_file, 'r');
        $message = new Zend_Mail_Message(array('file' => $fh));

        $this->assertEquals($message->subject, 'multipart');
    }

    public function testGetFirstPart()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }

    public function testGetFirstPartTwice()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $message->getPart(1);
        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }


    public function testGetWrongPart()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->expectException(Exception::class);
        $message->getPart(-1);
    }

    public function testNoHeaderMessage()
    {
        $message = new Zend_Mail_Message(array('file' => __FILE__));

        $this->assertEquals(substr($message->getContent(), 0, 5), '<?php');

        $raw     = file_get_contents(__FILE__);
        $raw     = "\t" . $raw;
        $message = new Zend_Mail_Message(array('raw' => $raw));

        $this->assertEquals(substr($message->getContent(), 0, 6), "\t<?php");
    }

    public function testMultipleHeader()
    {
        $raw     = file_get_contents($this->_file);
        $raw     = "sUBject: test\nSubJect: test2\n" . $raw;
        $message = new Zend_Mail_Message(array('raw' => $raw));

        $this->assertEquals(
            $message->getHeader('subject', 'string'),
            'test' . Zend_Mime::LINEEND . 'test2' . Zend_Mime::LINEEND . 'multipart'
        );
        $this->assertEquals($message->getHeader('subject'), array('test', 'test2', 'multipart'));
    }

    public function testContentTypeDecode()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertEquals(
            Zend_Mime_Decode::splitContentType($message->ContentType),
            array('type' => 'multipart/alternative', 'boundary' => 'crazy-multipart')
        );
    }

    public function testSplitEmptyMessage()
    {
        $this->assertEquals(Zend_Mime_Decode::splitMessageStruct('', 'xxx'), null);
    }

    public function testSplitInvalidMessage()
    {
        $this->expectException(Zend_Exception::class);
        Zend_Mime_Decode::splitMessageStruct("--xxx\n", 'xxx');
    }

    public function testInvalidMailHandler()
    {
        $this->expectException(Zend_Exception::class);
        $message = new Zend_Mail_Message(array('handler' => 1));
    }

    public function testMissingId()
    {
        $mail = new Zend_Mail_Storage_Mbox(array('filename' => dirname(__FILE__) . '/_files/test.mbox/INBOX'));

        $this->expectException(Zend_Exception::class);
        $message = new Zend_Mail_Message(array('handler' => $mail));
    }

    public function testIterator()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        foreach (new RecursiveIteratorIterator($message) as $num => $part) {
            if ($num == 1) {
                // explicit call of __toString() needed for PHP < 5.2
                $this->assertEquals(substr($part->__toString(), 0, 14), 'The first part');
            }
        }
        $this->assertEquals($part->contentType, 'text/x-vertical');
    }

    public function testDecodeString()
    {
        $string = '"Peter M=C3=BCller" <peter-mueller@example.com>';
        $is     = Zend_Mime_Decode::decodeQuotedPrintable($string);
        $should = quoted_printable_decode($string);
        $this->assertEquals($is, $should);
    }

    public function testSplitHeader()
    {
        $header = 'foo; x=y; y="x"';
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header), array('foo', 'x' => 'y', 'y' => 'x'));
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'x'), 'y');
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'y'), 'x');
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'foo', 'foo'), 'foo');
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'foo'), null);
    }

    public function testSplitInvalidHeader()
    {
        $header = '';
        $this->expectException(Zend_Exception::class);
        Zend_Mime_Decode::splitHeaderField($header);
    }

    public function testSplitMessage()
    {
        $header   = 'Test: test';
        $body     = 'body';
        $newlines = array("\r\n", "\n\r", "\n", "\r");

        foreach ($newlines as $contentEOL) {
            foreach ($newlines as $decodeEOL) {
                $content = $header . $contentEOL . $contentEOL . $body;
                $decoded = Zend_Mime_Decode::splitMessage($content, $decoded_header, $decoded_body, $decodeEOL);
                $this->assertEquals(array('test' => 'test'), $decoded_header);
                $this->assertEquals($body, $decoded_body);
            }
        }
    }

    public function testToplines()
    {
        $message = new Zend_Mail_Message(array('headers' => file_get_contents($this->_file)));
        $this->assertTrue(strpos($message->getToplines(), 'multipart message') === 0);
    }

    public function testNoContent()
    {
        $message = new Zend_Mail_Message(array('raw' => 'Subject: test'));

        $this->expectException(Zend_Exception::class);
        $message->getContent();
    }

    public function testEmptyHeader()
    {
        $message = new Zend_Mail_Message(array());
        $this->assertEquals(array(), $message->getHeaders());

        $message = new Zend_Mail_Message(array());
        $subject = null;
        try {
            $subject = $message->subject;
        } catch (Zend_Exception $e) {
            // ok
        }
        if ($subject) {
            $this->fail('no exception raised while getting header from empty message');
        }
    }

    public function testEmptyBody()
    {
        $message = new Zend_Mail_Message(array());
        $part    = null;
        try {
            $part = $message->getPart(1);
        } catch (Zend_Exception $e) {
            // ok
        }
        if ($part) {
            $this->fail('no exception raised while getting part from empty message');
        }

        $message = new Zend_Mail_Message(array());
        $this->assertTrue($message->countParts() == 0);
    }

    /**
     * @group ZF-5209
     */
    public function testCheckingHasHeaderFunctionality()
    {
        $message = new Zend_Mail_Message(array('headers' => array('subject' => 'foo')));

        $this->assertTrue($message->headerExists('subject'));
        $this->assertTrue(isset($message->subject));
        $this->assertTrue($message->headerExists('SuBject'));
        $this->assertTrue(isset($message->suBjeCt));
        $this->assertFalse($message->headerExists('From'));
    }

    public function testWrongMultipart()
    {
        $message = new Zend_Mail_Message(array('raw' => "Content-Type: multipart/mixed\r\n\r\ncontent"));

        $this->expectException(Zend_Exception::class);
        $message->getPart(1);
    }

    public function testLateFetch()
    {
        $mail = new Zend_Mail_Storage_Mbox(array('filename' => dirname(__FILE__) . '/_files/test.mbox/INBOX'));

        $message = new Zend_Mail_Message(array('handler' => $mail, 'id' => 5));
        $this->assertEquals($message->countParts(), 2);
        $this->assertEquals($message->countParts(), 2);

        $message = new Zend_Mail_Message(array('handler' => $mail, 'id' => 5));
        $this->assertEquals($message->subject, 'multipart');

        $message = new Zend_Mail_Message(array('handler' => $mail, 'id' => 5));
        $this->assertTrue(strpos($message->getContent(), 'multipart message') === 0);
    }

    public function testManualIterator()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));

        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 1);

        $message->next();
        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 2);

        $message->next();
        $this->assertFalse($message->valid());

        $message->rewind();
        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 1);
    }

    public function testMessageFlagsAreSet()
    {
        $origFlags = array(
            'foo' => 'bar',
            'baz' => 'bat'
        );
        $message = new Zend_Mail_Message(array('flags' => $origFlags));

        $messageFlags = $message->getFlags();
        $this->assertTrue($message->hasFlag('bar'), var_export($messageFlags, 1));
        $this->assertTrue($message->hasFlag('bat'), var_export($messageFlags, 1));
        $this->assertEquals(array('bar' => 'bar', 'bat' => 'bat'), $messageFlags);
    }

    public function testGetHeaderFieldSingle()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->assertEquals($message->getHeaderField('subject'), 'multipart');
    }

    public function testGetHeaderFieldDefault()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->assertEquals($message->getHeaderField('content-type'), 'multipart/alternative');
    }

    public function testGetHeaderFieldNamed()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->assertEquals($message->getHeaderField('content-type', 'boundary'), 'crazy-multipart');
    }

    public function testGetHeaderFieldMissing()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->assertNull($message->getHeaderField('content-type', 'foo'));
    }

    public function testGetHeaderFieldInvalid()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->expectException(Zend_Mail_Exception::class);
        $message->getHeaderField('fake-header-name', 'foo');
    }

    public function testCaseInsensitiveMultipart()
    {
        $message = new Zend_Mail_Message(array('raw' => "coNTent-TYpe: muLTIpaRT/x-empty\r\n\r\n"));
        $this->assertTrue($message->isMultipart());
    }

    public function testCaseInsensitiveField()
    {
        $header = 'test; fOO="this is a test"';
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'Foo'), 'this is a test');
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'bar'), null);
    }

    public function testSpaceInFieldName()
    {
        $header = 'test; foo =bar; baz      =42';
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'foo'), 'bar');
        $this->assertEquals(Zend_Mime_Decode::splitHeaderField($header, 'baz'), 42);
    }

    /**
     * @group ZF-11514
     */
    public function testConstructorMergesConstructorFlagsIntoDefaultFlags()
    {
        $message = new ZF11514_Mail_Message(array(
            'file'  => $this->_file,
            'flags' => array('constructor')
        ));
        $flags = $message->getFlags();
        $this->assertArrayHasKey('default', $flags);
        $this->assertEquals('yes!', $flags['default']);
        $this->assertArrayHasKey('constructor', $flags);
        $this->assertEquals('constructor', $flags['constructor']);
    }

    /**
     * @group ZF-3745
     */
    public function testBackwardsCompatibilityMaintainedWhenPartClassNotSpecified()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $this->assertGreaterThan(0, count($message));
        foreach ($message as $part) {
            $this->assertEquals('Zend_Mail_Part', get_class($part));
        }
    }

    /**
     * @group ZF-3745
     */
    public function testMessageAcceptsPartClassOverrideViaConstructor()
    {
        $message = new Zend_Mail_Message(array(
            'file'      => $this->_file,
            'partclass' => 'ZF3745_Mail_Part'
        ));
        $this->assertEquals('ZF3745_Mail_Part', $message->getPartClass());

        // Ensure message parts use the specified part class
        $this->assertGreaterThan(0, count($message));
        foreach ($message as $part) {
            $this->assertEquals('ZF3745_Mail_Part', get_class($part));
        }
    }

    /**
     * @group ZF-3745
     */
    public function testMessageAcceptsPartClassOverrideViaSetter()
    {
        $message = new Zend_Mail_Message(array('file' => $this->_file));
        $message->setPartClass('ZF3745_Mail_Part');
        $this->assertEquals('ZF3745_Mail_Part', $message->getPartClass());

        // Ensure message parts use the specified part class
        $this->assertGreaterThan(0, count($message));
        foreach ($message as $part) {
            $this->assertEquals('ZF3745_Mail_Part', get_class($part));
        }
    }

    public function invalidHeaders()
    {
        return array(
            'name'        => array("Fake\r\n\r\rnevilContent", 'value'),
            'value'       => array('Fake', "foo-bar\r\n\r\nevilContent"),
            'multi-value' => array('Fake', array('okay', "foo-bar\r\n\r\nevilContent")),
        );
    }

    /**
     * @dataProvider invalidHeaders
     * @group ZF2015-04
     */
    public function testRaisesExceptionWhenProvidedWithHeaderContainingCRLFInjection($name, $value)
    {
        $headers = array($name => $value);
        $this->expectException('Zend_Mail_Exception');
        $this->expectExceptionMessage('valid');
        $message = new Zend_Mail_Message(array(
            'headers' => $headers,
        ));
    }
}

/**
 * Message class which sets a pre-defined default flag set
 * @see ZF-11514
 */
class ZF11514_Mail_Message extends Zend_Mail_Message
{
    protected $_flags = array(
        'default' => 'yes!'
    );
}

class ZF3745_Mail_Part extends Zend_Mail_Part
{
}

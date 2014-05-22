<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Document;

use Mongator\Tests\TestCase;
use Mongator\Document\AbstractDocument as BaseAbstractDocument;

class AbstractDocument extends BaseAbstractDocument
{
    protected $eventPattern = 'foo.%s';

    public function setDocumentData($data)
    {
        $this->data = $data;
    }

    public function loadFull() {}
    public function isFieldInQuery($field) { return false; }
}

class AbstractDocumentTest extends TestCase
{
    public function testGetMongator()
    {
        $document = new AbstractDocument($this->mongator);
        $this->assertSame($this->mongator, $document->getMongator());
    }

    public function testCreate()
    {
        $this->assertEquals($this->mongator->create('Model\Article'), $this->mongator->create('Model\Article'));
    }

    public function testDocumentData()
    {
        $document = new AbstractDocument($this->mongator);
        $this->assertSame(array(), $document->getDocumentData());
        $data = array('fields' => array('foo' => 'bar'));
        $document->setDocumentData($data);
        $this->assertSame($data, $document->getDocumentData());
    }

    /**
     * @dataProvider providerDispatchEvent
     */
    public function testDispatchEvent($method, $expectedEvent)
    {
        $document = new AbstractDocument($this->mongator);

        $self = $this;
        $validator = function($arg) use ($document, $self) {
            $self->assertInstanceOf('Mongator\Document\Event', $arg);
            $self->assertSame($document, $arg->getDocument());

            return true;
        };

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($expectedEvent), $this->callback($validator));

        $this->mongator->setEventDispatcher($dispatcher);

        $document->$method();
    }

    public function providerDispatchEvent()
    {
        return array(
            array('preInsertEvent', 'foo.pre.insert'),
            array('postInsertEvent', 'foo.post.insert'),
            array('preUpdateEvent', 'foo.pre.update'),
            array('postUpdateEvent', 'foo.post.update'),
            array('preDeleteEvent', 'foo.pre.delete'),
            array('postDeleteEvent', 'foo.post.delete')
        );
    }

    /**
     * @dataProvider providerOnceDispatchEvent
     */
    public function testOnceDispatchEvent($method, $registerMethod)
    {
        $called = false;
        $document = new AbstractDocument($this->mongator);
        $document->$registerMethod(function() use (&$called) {
            $called = true;
        });
        $document->$method();

        $this->assertTrue($called);
    }

    public function providerOnceDispatchEvent()
    {
        return array(
            array('preInsertEvent', 'registerOncePreInsertEvent'),
            array('postInsertEvent', 'registerOncePostInsertEvent'),
            array('preUpdateEvent', 'registerOncePreUpdateEvent'),
            array('postUpdateEvent', 'registerOncePostUpdateEvent')

        );
    }
}

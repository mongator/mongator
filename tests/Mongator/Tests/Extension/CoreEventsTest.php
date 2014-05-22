<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;

class CoreEventsTest extends TestCase
{
    public function testDocumentSaveEventsInsert()
    {
        $documents = array(
            $this->mongator->create('Model\Events')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\Events')->setName('bar')->setMyEventPrefix('1'),
        );
        $this->mongator->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreInserting11',
            '2PostInserting00',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting11',
            '1PostInserting00',
        ), $documents[1]->getEvents());
    }

    public function testDocumentSaveOnceEventsInsert()
    {
        $documents = array(
            $this->mongator->create('Model\Events')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\Events')->setName('bar')->setMyEventPrefix('1'),
        );

        $documents[0]->registerOncePreInsertEvent(function($document) {
            $document->addEvent('OncePreInsert');
        });

        $documents[1]->registerOncePostInsertEvent(function($document) {
            $document->addEvent('OncePostInsert');
        });

        $this->mongator->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreInserting11',
            '2OncePreInsert11',
            '2PostInserting00',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting11',
            '1PostInserting00',
            '1OncePostInsert00',
        ), $documents[1]->getEvents());
    }

    public function testDocumentSaveEventsUpdate()
    {
        $documents = array(
            $this->mongator->create('Model\Events')->setName('foo')->save()->clearEvents()->setName('bar')->setMyEventPrefix('2')->save(),
            $this->mongator->create('Model\Events')->setName('bar')->save()->clearEvents()->setName('foo')->setMyEventPrefix('1')->save()
        );

        $this->mongator->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreUpdating01',
            '2PostUpdating00',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating01',
            '1PostUpdating00',
        ), $documents[1]->getEvents());
    }

    public function testDocumentSaveOnceEventsUpdate()
    {
        $documents = array(
            $this->mongator->create('Model\Events')->setName('foo')->save()->clearEvents(),
            $this->mongator->create('Model\Events')->setName('bar')->save()->clearEvents()
        );

        $documents[0]->registerOncePreUpdateEvent(function($document) {
            $document->addEvent('OncePreUpdate');
        });

        $documents[1]->registerOncePostUpdateEvent(function($document) {
            $document->addEvent('OncePostUpdate');
        });

        $documents[0]->setName('bar')->setMyEventPrefix('2')->save();
        $documents[1]->setName('foo')->setMyEventPrefix('1')->save();

        $this->mongator->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreUpdating01',
            '2OncePreUpdate01',
            '2PostUpdating00',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating01',
            '1PostUpdating00',
            '1OncePostUpdate00'
        ), $documents[1]->getEvents());
    }

    public function testDocumentDeleteEventsSingleDocument()
    {
        $document = $this->mongator->create('Model\Events')->setName('foo')->save()->clearEvents()->setMyEventPrefix('ups')->setName('bar');
        $document->delete();

        $this->assertSame(array(
            'upsPreDeleting01',
            'upsPostDeleting01',
        ), $document->getEvents());
    }
}

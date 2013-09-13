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

    public function testEmbeddedSaveEventsInsert()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $document->addEmbedded($embeddeds)->save();

        $this->assertSame(array(
            '2PreInserting1',
            '2PostInserting0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting1',
            '1PostInserting0',
        ), $embeddeds[1]->getEvents());
    }

    public function testEmbeddedSaveEventsInsertOldDocument()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');
        $document->save();

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $document->addEmbedded($embeddeds)->save();

        $this->assertSame(array(
            '2PreInserting1',
            '2PostInserting0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting1',
            '1PostInserting0',
        ), $embeddeds[1]->getEvents());
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


    public function testEmbeddedSaveOnceEventsInsert()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $embeddeds[0]->registerOncePreInsertEvent(function($document) {
            $document->addEvent('OncePreInsert');
        });

        $embeddeds[1]->registerOncePostInsertEvent(function($document) {
            $document->addEvent('OncePostInsert');
        });

        $document->addEmbedded($embeddeds)->save();

        $this->assertSame(array(
            '2PreInserting1',
            '2OncePreInsert1',
            '2PostInserting0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting1',
            '1PostInserting0',
            '1OncePostInsert0'
        ), $embeddeds[1]->getEvents());
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

    public function testEmbeddedSaveEventsUpdate()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $document->addEmbedded($embeddeds)->save();

        $embeddeds[0]->clearEvents()->setName('bar')->setMyEventPrefix('2');
        $embeddeds[1]->clearEvents()->setName('bar')->setMyEventPrefix('1');

        $document->save();

        $this->assertSame(array(
            '2PreUpdating1',
            '2PostUpdating0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating0',
            '1PostUpdating0',
        ), $embeddeds[1]->getEvents());
    }

    public function testEmbeddedSaveEventsUpdateOldDocument()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');
        $document->save();

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $document->addEmbedded($embeddeds)->save();

        $embeddeds[0]->clearEvents()->setName('bar')->setMyEventPrefix('2');
        $embeddeds[1]->clearEvents()->setName('bar')->setMyEventPrefix('1');

        $document->save();

        $this->assertSame(array(
            '2PreUpdating1',
            '2PostUpdating0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating0',
            '1PostUpdating0',
        ), $embeddeds[1]->getEvents());
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

    public function testEmbeddedSaveOnceEventsUpdate()
    {
        $document = $this->mongator->create('Model\EventsEmbeddedMany');

        $embeddeds = array(
            $this->mongator->create('Model\EmbeddedEvents')->setName('foo')->setMyEventPrefix('2'),
            $this->mongator->create('Model\EmbeddedEvents')->setName('bar')->setMyEventPrefix('1'),
        );

        $document->addEmbedded($embeddeds)->save();

        $embeddeds[0]->registerOncePreUpdateEvent(function($document) {
            $document->addEvent('OncePreUpdate');
        });

        $embeddeds[1]->registerOncePostUpdateEvent(function($document) {
            $document->addEvent('OncePostUpdate');
        });

        $embeddeds[0]->clearEvents()->setName('bar')->setMyEventPrefix('2');
        $embeddeds[1]->clearEvents()->setName('foo')->setMyEventPrefix('1');

        $document->save();

        $this->assertSame(array(
            '2PreUpdating1',
            '2OncePreUpdate1',
            '2PostUpdating0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating1',
            '1PostUpdating0',
            '1OncePostUpdate0'
        ), $embeddeds[1]->getEvents());

        $embeddeds[0]->clearEvents()->setName('foo')->setMyEventPrefix('2');
        $embeddeds[1]->clearEvents()->setName('bar')->setMyEventPrefix('1');
        
        $document->save();

        $this->assertSame(array(
            '2PreUpdating1',
            '2PostUpdating0',
        ), $embeddeds[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating1',
            '1PostUpdating0'
        ), $embeddeds[1]->getEvents());
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

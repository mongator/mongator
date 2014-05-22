<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <mcuadros@gmail.com>
 * (c) Eduardo Gulias <me@egulias.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;
use Mongator\Group\EmbeddedGroup;

class CoreEventTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->mongator->setEventDispatcher($this->dispatcher);
    }

    public function testInsertEvents()
    {
        $this->assertDispatchEvent('mongator.model.article.pre.insert', 0);
        $this->assertDispatchEvent('mongator.model.article.post.insert', 1);

        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->save();
    }

    public function testUpdateEvents()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->save();

        $this->assertDispatchEvent('mongator.model.article.pre.update', 0);
        $this->assertDispatchEvent('mongator.model.article.post.update', 1);

        $article->setTitle('bar');
        $article->save();
    }

    public function testDeleteEvents()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->save();

        $this->assertDispatchEvent('mongator.model.article.pre.delete', 0);
        $this->assertDispatchEvent('mongator.model.article.post.delete', 1);

        $article->delete();
    }

    public function testInsertEventsWithEmbeddeds()
    {
        $this->assertDispatchEvent('mongator.model.article.pre.insert', 0);
        $this->assertDispatchEvent('mongator.model.comment.pre.insert', 1);
        $this->assertDispatchEvent('mongator.model.comment.post.insert', 2);
        $this->assertDispatchEvent('mongator.model.article.post.insert', 3);

        $comment = $this->mongator->create('Model\Comment');

        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->addComments($comment);
        $article->save();
    }

    public function testInsertDocumentInsertEmbeddedEvents()
    {
        $this->assertDispatchEvent('mongator.model.article.pre.insert', 0);
        $this->assertDispatchEvent('mongator.model.comment.pre.insert', 1);
        $this->assertDispatchEvent('mongator.model.comment.post.insert', 2);
        $this->assertDispatchEvent('mongator.model.article.post.insert', 3);

        $comment = $this->mongator->create('Model\Comment');
        $comment->setName('test');

        $article = $this->mongator->create('Model\Article');
        $article->addComments($comment);
        $article->save();
    }

    public function testUpdateDocumentInsertEmbeddedEvents()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->save();

        $this->assertDispatchEvent('mongator.model.article.pre.update', 0);
        $this->assertDispatchEvent('mongator.model.comment.pre.insert', 1);
        $this->assertDispatchEvent('mongator.model.comment.post.insert', 2);
        $this->assertDispatchEvent('mongator.model.article.post.update', 3);

        $comment = $this->mongator->create('Model\Comment');
        $comment->setName('test');

        $article->addComments($comment);
        $article->save();
    }

    public function testUpdateDocumentUpdateEmbeddedEvents()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');

        $comment = $this->mongator->create('Model\Comment');
        $comment->setName('test');

        $article->addComments($comment);
        $article->save();

        $this->assertDispatchEvent('mongator.model.article.pre.update', 0);
        $this->assertDispatchEvent('mongator.model.comment.pre.update', 1);
        $this->assertDispatchEvent('mongator.model.comment.post.update', 2);
        $this->assertDispatchEvent('mongator.model.article.post.update', 3);

        $comments = $article->getComments()->one()->setName('foo');
        $article->save();
    }

    private function assertDispatchEvent($eventName, $at)
    {
        $assert = function($arg) {
            $this->assertInstanceOf('Mongator\Document\Event', $arg);
        
            return true;
        };

        $this->dispatcher
            ->expects($this->at($at))
            ->method('dispatch')
            ->with($this->equalTo($eventName), $this->callback($assert));
    }
}

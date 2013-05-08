<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Group;

use Mongator\Tests\TestCase;
use Mongator\Group\EmbeddedGroup;

class EmbeddedGroupTest extends TestCase
{
    public function testInitializeSaved()
    {
        $data = array(
            array('name' => 'foo'),
            array('name' => 'bar'),
        );

        $group = new EmbeddedGroup('Model\Comment');
        $group->setRootAndPath($article = $this->mongator->create('Model\Article'), 'comments');
        $group->setSavedData($data);
        $this->assertSame(2, $group->count());
        $saved = $group->getSaved();
        $this->assertEquals($this->mongator->create('Model\Comment')->setDocumentData($data[0]), $saved[0]);
        $this->assertSame(array('root' => $article, 'path' => 'comments.0'), $saved[0]->getRootAndPath());
        $this->assertEquals($this->mongator->create('Model\Comment')->setDocumentData($data[0]), $saved[0]);
        $this->assertSame(array('root' => $article, 'path' => 'comments.1'), $saved[1]->getRootAndPath());
    }

    public function testRootAndPath()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $comment = $this->mongator->create('Model\Comment');
        $group->add($comment);
        $group->setRootAndPath($article = $this->mongator->create('Model\Article'), 'comments');
        $this->assertSame(array('root' => $article, 'path' => 'comments._add0'), $comment->getRootAndPath());
    }

    public function testAdd()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $group->setRootAndPath($article = $this->mongator->create('Model\Article'), 'comments');
        $comment = $this->mongator->create('Model\Comment');
        $group->add($comment);
        $this->assertSame(array('root' => $article, 'path' => 'comments._add0'), $comment->getRootAndPath());
    }

    public function testSavedData()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $this->assertNull($group->getSavedData());
        $group->setSavedData($data = array(array('foo' => 'bar'), array('bar' => 'foo')));
        $this->assertSame($data, $group->getSavedData());
    }
    
    public function testDuplicateSplObjectHash()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $comment = $this->mongator->create('Model\Comment');
        $this->assertEquals(0, count($group->getAdd()));
        $group->add($comment);
        $this->assertEquals(1, count($group->getAdd()));
        unset($group);
        $group = new EmbeddedGroup('Model\Comment');
        $this->assertEquals(0, count($group->getAdd()));
    }

    public function testDataAfterDocumentSave() {
        $article = $this->mongator->create('Model\Article');
        $group = $article->getComments();
        $comments = array();
        for ($i = 0; $i < 3; $i++) {
            $comments[] = $comment = $this->mongator->create('Model\Comment');
            $comment->setName("Name $i");
            $comment->setDate(new \Datetime());
            $article->addComments($comment);
        }

        $article->save();
        $this->assertEquals(3, count($article->getComments()->all()));
        foreach ($article->getComments()->all() as $key => $comment) {
            $this->assertSame($comments[$key]->getName(), $comment->getName());
            $this->assertSame(
                $comments[$key]->getDate()->getTimestamp(),
                $comment->getDate()->getTimestamp()
            );

            $rap = $comment->getRootAndPath();
            $this->assertSame($article, $rap['root']);
            $this->assertSame("comments.$key", $rap['path']);
        }

        $article->delete();
    }
}

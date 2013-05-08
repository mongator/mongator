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

class DocumentArrayAccessTest extends TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testOffsetExists()
    {
        $article = $this->mongator->create('Model\Article');
        isset($article['title']);
    }

    public function testOffsetSet()
    {
        $article = $this->mongator->create('Model\Article');
        $article['title'] = 'foo';
        $this->assertSame('foo', $article->getTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetNameNotExists()
    {
        $article = $this->mongator->create('Model\Article');
        $article['no'] = 'foo';
    }

    public function testOffsetGet()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('bar');
        $this->assertSame('bar', $article['title']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetGetNameNotExists()
    {
        $article = $this->mongator->create('Model\Article');
        $article['no'];
    }

    /**
     * @expectedException \LogicException
     */
    public function testOffsetUnset()
    {
        $article = $this->mongator->create('Model\Article');
        unset($article['title']);
    }
}

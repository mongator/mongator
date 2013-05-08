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

class DocumentPropertyOverloadingTest extends TestCase
{
    public function test__set()
    {
        $article = $this->mongator->create('Model\Article');
        $article->title = 'foo';
        $this->assertSame('foo', $article->getTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test__setNameNotExists()
    {
        $article = $this->mongator->create('Model\Article');
        $article->no = 'foo';
    }

    public function test__get()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $this->assertSame('foo', $article->title);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test__getNameNotExists()
    {
        $article = $this->mongator->create('Model\Article');
        $article->no;
    }
}

<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests;

use Mongator\Query\Result;

class ResultTest extends TestCase
{
    public function testConstructFromArrayObject() {
        $ao = new \ArrayObject(range(0,19));
        $result = new Result($ao);

        $valid = range(0,19);
        foreach ($result as $key => $value) {
            $this->assertSame($key, $value);
            $this->assertSame($valid[$key], $value);
        }

        $result->rewind();
        $this->assertSame(0, $result->current());
        $this->assertSame(0, $result->key());

        $this->assertSame(20, $result->count());
    }

    public function testConstructFromMongoCursor() {
        $articles = $this->createArticlesRaw(10);

        $query = new \Model\ArticleQuery($this->mongator->getRepository('Model\Article'));

        $result = $query->limit(2)->createCursor();
        $this->assertInstanceOf('MongoCursor', $result->getIterator());

        $i = 0;
        foreach ($result as $key => $value) {
            $this->assertSame('Article' . $i++, $value['title']);
            $this->assertSame($key, (string)$value['_id']);
        }

        $this->assertSame(10, $result->count());
    }

    public function testSetCountInteger() {
        $ao = new \ArrayObject(range(0,19));
        $result = new Result($ao);

        $result->setCount(50);
        $this->assertSame(50, $result->count());
    }

    public function testSetCountClosure() {
        $ao = new \ArrayObject(range(0,19));
        $result = new Result($ao);

        $result->setCount(function() { return 100; });
        $this->assertSame(100, $result->count());
    }

    public function testSerialize() {
        $ao = new \ArrayObject(range(0,2));
        $result = new Result($ao);

        $result->setCount(50);

        $expected = 'C:21:"Mongator\Query\Result":64:{a:2:{s:5:"count";i:50;s:4:"data";a:3:{i:0;i:0;i:1;i:1;i:2;i:2;}}}';
        $this->assertSame($expected, serialize($result));
    }

    public function testUnSerialize() {
        $ao = new \ArrayObject(range(0,2));
        $expected = new Result($ao);

        $expected->setCount(50);

        $string = 'C:21:"Mongator\Query\Result":64:{a:2:{s:5:"count";i:50;s:4:"data";a:3:{i:0;i:0;i:1;i:1;i:2;i:2;}}}';
        $this->assertEquals($expected, unserialize($string));
    }
}

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
}

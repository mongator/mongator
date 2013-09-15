<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests;

use Mongator\Archive;

class ArchiveTest extends TestCase
{
    public function testHasGetSet()
    {
        $archive = new Archive();

        $this->assertFalse($archive->has('field1'));
        $this->assertFalse($archive->has('field2'));
        $this->assertFalse($archive->has('field3'));

        $archive->set('field1', 'foo');
        $archive->set('field3', 'bar');
        $archive->set('field2', 'ups');

        $this->assertTrue($archive->has('field1'));
        $this->assertTrue($archive->has('field3'));
        $this->assertTrue($archive->has('field2'));

        $this->assertSame('foo', $archive->get('field1'));
        $this->assertSame('bar', $archive->get('field3'));
        $this->assertSame('ups', $archive->get('field2'));

        $archive->remove('field1');
        $this->assertFalse($archive->has('field1'));
        $this->assertTrue($archive->has('field3'));
        $this->assertTrue($archive->has('field2'));
    }

    public function testGetByRef()
    {
        $archive = new Archive();

        $fieldKey =& $archive->getByRef('field1', array());
        $this->assertSame(array(), $fieldKey);

        $fieldKey['foo'] = 'bar';
        $this->assertSame($fieldKey, $archive->get('field1'));
    }

    public function testGetOrDefault()
    {
        $archive = new Archive();

        $this->assertSame('foobar', $archive->getOrDefault('field1', 'foobar'));

        $archive->set('field2', 'ups');
        $this->assertSame('ups', $archive->getOrDefault('field2', 'foobar'));
    }
}

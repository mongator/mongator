<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Id;

use Mongator\Tests\TestCase;
use Mongator\Id\BaseIdGenerator;
use Mongator\Id\IdGeneratorContainer;

class TestingIdGenerator extends BaseIdGenerator
{
    public function getCode(array $options)
    {
    }

    public function getToMongoCode()
    {
    }
}

class IdGeneratorContainerTest extends TestCase
{
    public function testHas()
    {
        $this->assertTrue(IdGeneratorContainer::has('native'));
        $this->assertFalse(IdGeneratorContainer::has('no'));
    }

    public function testAdd()
    {
        IdGeneratorContainer::add('testing', 'Mongator\Tests\Id\TestingIdGenerator');
        $this->assertTrue(IdGeneratorContainer::has('testing'));

        $this->assertInstanceOf('Mongator\Tests\Id\TestingIdGenerator', IdGeneratorContainer::get('testing'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAlreadyExists()
    {
        IdGeneratorContainer::add('native', 'Mongator\Tests\Id\TestingIdGenerator');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddClassNotSubclassType()
    {
        IdGeneratorContainer::add('testing', '\DateTime');
    }

    public function testGet()
    {
        $native = IdGeneratorContainer::get('native');
        $sequence = IdGeneratorContainer::get('sequence');

        $this->assertInstanceOf('Mongator\Id\NativeIdGenerator', $native);
        $this->assertInstanceOf('Mongator\Id\SequenceIdGenerator', $sequence);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNotExists()
    {
        IdGeneratorContainer::get('no');
    }

    public function testRemove()
    {
        IdGeneratorContainer::remove('native');
        $this->assertFalse(IdGeneratorContainer::has('native'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveNotExists()
    {
        IdGeneratorContainer::remove('no');
    }

    public function testResetTypes()
    {
        IdGeneratorContainer::add('testing', 'Mongator\Tests\Id\TestingIdGenerator');
        IdGeneratorContainer::reset();

        $this->assertTrue(IdGeneratorContainer::has('native'));
        $this->assertFalse(IdGeneratorContainer::has('testing'));
    }
}

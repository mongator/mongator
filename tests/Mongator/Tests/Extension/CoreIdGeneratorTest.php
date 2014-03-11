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

class CoreIdGeneratorTest extends TestCase
{
    public function testNoneIdGeneratorManual()
    {
        $document = $this->mongator->create('Model\NoneIdGenerator');
        $document->setId('my_id');
        $document->setName('ups');
        $document->save();

        $expected = array('_id' => 'my_id', 'name' => 'ups');
        $document = $document
            ->getRepository()
            ->getCollection()
            ->findOne(array('_id' => 'my_id'));

        $this->assertEmpty(array_diff($expected, $document));
    }

    public function testNoneIdGeneratorMongo()
    {
        $document = $this->mongator->create('Model\NoneIdGenerator');
        $document->setName('ups');
        $document->save();

        $this->assertInstanceOf('MongoId', $document->getId());
    }

    public function testNativeIdGenerator()
    {
        $document = $this->mongator->create('Model\NativeIdGenerator');
        $document->setName('ups');
        $document->save();

        $this->assertInstanceOf('MongoId', $document->getId());
    }

    public function testSequenceIdGenerator()
    {
        $document1 = $this->mongator->create('Model\SequenceIdGenerator');
        $document1->setName('ups');
        $document1->save();
        $this->assertSame(1, $document1->getId());

        $document2 = $this->mongator->create('Model\SequenceIdGenerator');
        $document2->setName('ups');
        $document2->save();
        $this->assertSame(2, $document2->getId());

        $document3 = $this->mongator->create('Model\SequenceIdGenerator');
        $document3->setName('ups');
        $document3->save();
        $this->assertSame(3, $document3->getId());

        $document4 = $this->mongator->create('Model\SequenceIdGenerator2');
        $document4->setName('ups');
        $document4->save();
        $this->assertSame(1, $document4->getId());

        $document5 = $this->mongator->create('Model\SequenceIdGenerator2');
        $document5->setName('ups');
        $document5->save();
        $this->assertSame(2, $document5->getId());
    }

    public function testSequenceIdGeneratorDescending()
    {
        $document1 = $this->mongator->create('Model\SequenceIdGeneratorDescending');
        $document1->setName('ups');
        $document1->save();
        $this->assertSame(-1, $document1->getId());

        $document2 = $this->mongator->create('Model\SequenceIdGeneratorDescending');
        $document2->setName('ups');
        $document2->save();
        $this->assertSame(-2, $document2->getId());

        $document3 = $this->mongator->create('Model\SequenceIdGeneratorDescending');
        $document3->setName('ups');
        $document3->save();
        $this->assertSame(-3, $document3->getId());
    }

    public function testSequenceIdGeneratorStart()
    {
        $document1 = $this->mongator->create('Model\SequenceIdGeneratorStart');
        $document1->setName('ups');
        $document1->save();
        $this->assertSame(2000, $document1->getId());

        $document2 = $this->mongator->create('Model\SequenceIdGeneratorStart');
        $document2->setName('ups');
        $document2->save();
        $this->assertSame(2001, $document2->getId());

        $document3 = $this->mongator->create('Model\SequenceIdGeneratorStart');
        $document3->setName('ups');
        $document3->save();
        $this->assertSame(2002, $document3->getId());
    }

    public function testIdGeneratorSingleInheritance()
    {
        $grandParent = $this->mongator->create('Model\IdGeneratorSingleInheritanceGrandParent')->setName('foo')->save();
        $parent = $this->mongator->create('Model\IdGeneratorSingleInheritanceParent')->setName('foo')->save();
        $child = $this->mongator->create('Model\IdGeneratorSingleInheritanceChild')->setName('foo')->save();

        $this->assertSame(1, $grandParent->getId());
        $this->assertSame(2, $parent->getId());
        $this->assertSame(3, $child->getId());
    }

    public function testABIdGenerator()
    {
        $document = $this->mongator->create('Model\ABIdGenerator')->setName('foo')->save();
        $id = $document->getId();
        $this->assertInstanceOf('Mongator\Tests\Id\ABId', $id);
        $identityMap = $this->mongator->getRepository('Model\ABIdGenerator')->getIdentityMap();
        $this->assertTrue($identityMap->has($id));
    }
}

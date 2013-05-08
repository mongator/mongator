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

use Mongator\IndexManager;

class IndexManagerTest extends TestCase
{
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->mongator->getRepository('Model\Article');
        $this->indexManager = new IndexManager($this->repository);
    }

    public function testConstructAndGetRepository()
    {
        $this->assertSame($this->repository, $this->indexManager->getRepository());
    }

    public function testGetConfig()
    {
        $config = $this->repository->getMongator()->getMetadataFactory()->getClass('Model\Article');

        $this->assertSame($config['_indexes'], $this->indexManager->getConfig());
    }

    public function testDiff()
    {
        $present = array('keys' => array('slug' => 1), 'options' => array('unique' => true));
        $unknown = array('keys' => array('loc' => '2d'));
        $missing = array('keys' => array('authorId' => 1, 'isActive' => 1), 'options' => array());

        $this->repository->getCollection()->ensureIndex($present['keys'], $present['options']);
        $this->repository->getCollection()->ensureIndex($unknown['keys']);


        $diff = $this->indexManager->getDiff(); 
        $this->assertCount(9, $diff['missing']);
        $this->assertCount(1, $diff['present']);
        $this->assertCount(1, $diff['unknown']);

        $this->assertSame($missing, $diff['missing']['authorId_1_isActive_1']);
        $this->assertSame($present, $diff['present']['slug_1_unique_1']);
    }

    public function testCommit()
    {
        $present = array('keys' => array('slug' => 1), 'options' => array('unique' => true));
        $unknown = array('keys' => array('loc' => '2d'));
        $missing = array('keys' => array('authorId' => 1, 'isActive' => 1), 'options' => array());

        $this->repository->getCollection()->ensureIndex($present['keys'], $present['options']);
        $this->repository->getCollection()->ensureIndex($unknown['keys']);

        $this->assertTrue($this->indexManager->commit());
        $this->assertCount(11, $this->repository->getCollection()->getIndexInfo());
    }
}

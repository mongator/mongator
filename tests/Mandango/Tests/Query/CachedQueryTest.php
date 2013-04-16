<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests;

use Mandango\Query\CacheQuery;

class CachedQueryTest extends TestCase
{
    protected $identityMap;
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->identityMap = $this->mandango->getRepository('Model\Cached')->getIdentityMap();
        $this->query = new \Model\CachedQuery($this->mandango->getRepository('Model\Cached'));
    }

    public function testConstructor()
    {
        $query = new \Model\CategoryQuery($repository = $this->mandango->getRepository('Model\Cached'));
        $this->assertSame($repository, $query->getRepository());
        $hash = $query->getHash();
        $this->assertInternalType('string', $hash);
        $this->assertSame($hash, $query->getHash());
    }

    public function testExecute()
    {
        $messages = $this->createCachedRaw(10);
        $this->mandango->getRepository('Model\Cached')->ensureIndexes();

        $query = new \Model\CachedQuery($this->mandango->getRepository('Model\Cached'));
        $query
            ->limit(5)
            ->fields(array('author' => 1));

        $result = $query->execute();
        $this->assertInstanceOf('Mandango\Query\Result', $result);

        $this->assertSame(10, $query->count());

        $messages = $this->createCachedRaw(10);
        $result = $query->execute();
        $this->assertInstanceOf('Mandango\Query\Result', $result);

        $this->assertSame(10, $query->count());
        $this->assertSame(10, $query->count());
        $this->assertSame(10, $result->count());

        foreach($query->all() as $key => $document) {
            $this->assertSame($key, (string)$document->getId());
            $this->assertInstanceOf('Model\Cached', $document);
        }

        usleep(1200000);
        $result = $query->execute();
        $this->assertInstanceOf('Mandango\Query\Result', $result);

        $this->assertSame(20, $query->count());
        $this->assertSame(20, $query->count());
        $this->assertSame(20, $result->count());

    }


}

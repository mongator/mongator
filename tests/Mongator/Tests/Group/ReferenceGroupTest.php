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
use Mongator\Group\ReferenceGroup;

class ReferenceGroupTest extends TestCase
{
    public function testConstructor()
    {
        $group = new ReferenceGroup('Model\Category', $article = $this->mongator->create('Model\Article'), 'category_ids');
        $this->assertSame($article, $group->getParent());
        $this->assertSame('category_ids', $group->getField());
    }
}

<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Cache;

use Mongator\Cache\ArrayCache;

class ArrayCacheTest extends Cache
{
    protected function getCacheDriver()
    {
        return new ArrayCache();
    }
}

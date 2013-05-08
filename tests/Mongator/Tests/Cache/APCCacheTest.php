<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Cache;

use Mongator\Cache\APCCache;

class APCCacheTest extends Cache
{
    protected function getCacheDriver()
    {
       if ( function_exists('apc_fetch') == false ) {
            $this->markTestSkipped(
              'apc extension must be loaded'
            );
        }

        if ( (boolean)ini_get('apc.enabled') == false ) {
            $this->markTestSkipped(
              'apc.enable must be true'
            );
        }

        if ( (boolean)ini_get('apc.enable_cli') == false ) {
            $this->markTestSkipped(
              'apc.enable_cli must be true'
            );
        }

        return new APCCache();
    }
}

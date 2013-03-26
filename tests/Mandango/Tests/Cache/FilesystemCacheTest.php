<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Cache;

use Mandango\Cache\FilesystemCache;

class FilesystemCacheTest extends Cache
{
    private $folder;
    protected function getCacheDriver()
    {
        $this->folder = sys_get_temp_dir().'/mandango_filesystem_cache_tests'.mt_rand(111111, 999999);
        return new FilesystemCache($this->folder);
    }

    public function testGetWithFileCache() {
        $this->cache->set('read', 'foo');
        $this->assertSame('foo', $this->cache->get('read'));

        unlink($this->folder . '/read.php');
        $this->assertSame('foo', $this->cache->get('read'));
    }
}

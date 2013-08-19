<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Cache;

/**
 * FilesystemCache.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class FilesystemCache extends AbstractCache
{
    private $dir;
    private $data = array();

    /**
     * Constructor.
     *
     * @param string $dir The directory.
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create the "%s" directory.', $dir));
        }

    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $content = $this->pack($key, $value, $ttl);
        $file = $this->dir.'/'.$key.'.php';

        $valueExport = var_export($content , true);

        $php = sprintf("<?php\nreturn %s;\n", $valueExport);

        if (false === @file_put_contents($file, $php, LOCK_EX)) {
            throw new \RuntimeException(sprintf('Unable to write the "%s" file.', $file));
        }

        $this->data[$key] = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $file = $this->dir.'/'.$key.'.php';
        if (file_exists($file) && false === @unlink($file)) {
            throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file));
        }

        if ( isset($this->data[$key]) ) unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = array();

        if (is_dir($this->dir)) {
            foreach (new \DirectoryIterator($this->dir) as $file) {
                if ($file->isFile()) {
                    if (false === @unlink($file->getRealPath())) {
                        throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file->getRealPath()));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($key)
    {
        if ( isset($this->data[$key]) ) return $this->data[$key];

        $file = $this->dir.'/'.$key.'.php';
        if ( !file_exists($file) ) return null;
        return $this->data[$key] = require($file);
    }
}

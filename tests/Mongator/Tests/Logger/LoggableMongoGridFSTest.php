<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Logger;

use Mongator\Tests\TestCase;
use Mongator\Logger\LoggableMongo;
use Mongator\Logger\LoggableMongoGridFS;

class LoggableMongoGridFSTest extends TestCase
{
    protected $log;
  
    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }
    }

    public function testConstructorAndGetDB()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('Mongator_logger');

        $grid = new LoggableMongoGridFS($db, 'Mongator_logger_grid');

        $this->assertSame('Mongator_logger_grid.files', $grid->getName());
        $this->assertSame($db, $grid->getDB());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('Mongator_logger');
        $grid = $db->getGridFS('Mongator_logger_grid');

        $grid->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'Mongator_logger',
            'collection' => 'Mongator_logger_grid.files',
            'gridfs'     => 1
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testFind()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('Mongator_logger');
        $grid = $db->getGridFS('Mongator_logger_grid');

        $cursor = $grid->find();
        $this->assertInstanceOf('\Mongator\Logger\LoggableMongoGridFSCursor', $cursor);

        $cursor = $grid->find($query = array('foo' => 'bar'), $fields = array('foobar' => 1, 'barfoo' => 1));
        $info = $cursor->info();
        $this->assertSame($query, $info['query']);
        $this->assertSame($fields, $info['fields']);
    }
}

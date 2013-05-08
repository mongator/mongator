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

use Mongator\Logger\LoggableMongo;
use Mongator\Logger\LoggableMongoDB;

class LoggableMongoDBTest extends \PHPUnit_Framework_TestCase
{
    protected $log;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }
    }

    public function testConstructorAndGetMongo()
    {
        $mongo = new LoggableMongo();

        $db = new LoggableMongoDB($mongo, 'mongator_logger');

        $this->assertSame('mongator_logger', $db->__toString());
        $this->assertSame($mongo, $db->getMongo());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mongator_logger');

        $db->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database' => 'mongator_logger'
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testSelectCollection()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mongator_logger');

        $collection = $db->selectCollection('mongator_logger_collection');

        $this->assertInstanceOf('\Mongator\Logger\LoggableMongoCollection', $collection);
        $this->assertSame('mongator_logger_collection', $collection->getName());
    }

    public function test__get()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mongator_logger');

        $collection = $db->mongator_logger_collection;

        $this->assertInstanceOf('\Mongator\Logger\LoggableMongoCollection', $collection);
        $this->assertSame('mongator_logger_collection', $collection->getName());
    }

    public function testGetGridFS()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mongator_logger');

        $grid = $db->getGridFS('mongator_logger_grid');

        $this->assertInstanceOf('\Mongator\Logger\LoggableMongoGridFS', $grid);
        $this->assertSame('mongator_logger_grid.files', $grid->getName());
    }
}

<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator;

/**
 * ConnectionInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface ConnectionInterface
{
    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable The logger callable.
     *
     * @throws \RuntimeException When the connection has the Mongo already.
     *
     * @api
     */
    public function setLoggerCallable($loggerCallable = null);

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     *
     * @api
     */
    public function getLoggerCallable();

    /**
     * Set the log default.
     *
     * @param array $logDefault The log default.
     *
     * @throws \RuntimeException When the connection has the Mongo already.
     *
     * @api
     */
    public function setLogDefault(array $logDefault);

    /**
     * Returns the log default.
     *
     * @return array|null The log default.
     *
     * @api
     */
    public function getLogDefault();

    /**
     * Returns the mongo connection object.
     *
     * @return \MongoClient The mongo collection object.
     *
     * @api
     */
    public function getMongo();

    /**
     * Returns the database object.
     *
     * @return \MongoDB The database object.
     *
     * @api
     */
    public function getMongoDB();
}

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
 * UnitOfWorkInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface UnitOfWorkInterface
{
    /**
     * Persist a document.
     *
     * @param \Mongator\Document\Document|array $documents A document or an array of documents.
     *
     * @api
     */
    public function persist($documents);

    /**
     * Remove a document.
     *
     * @param \Mongator\Document\Document|array $documents A document or an array of documents.
     *
     * @api
     */
    public function remove($documents);

    /**
     * Commit pending persist and remove operations.
     *
     * @api
     */
    public function commit();

    /**
     * Clear the pending operations
     *
     * @api
     */
    public function clear();
}

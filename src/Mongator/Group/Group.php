<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Group;

use Mongator\Archive;
use Mongator\Document\Document;

/**
 * Group.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class Group extends AbstractGroup
{
    /**
     * Constructor.
     *
     * @param string $documentClass The document class.
     *
     * @api
     */
    public function __construct($documentClass)
    {
        parent::__construct();
        $this->getArchive()->set('document_class', $documentClass);
    }

    /**
     * Returns the document class.
     *
     * @api
     */
    public function getDocumentClass()
    {
        return $this->getArchive()->get('document_class');
    }
}

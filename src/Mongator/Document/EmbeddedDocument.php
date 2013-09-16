<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Document;

use Mongator\Archive;
use Mongator\Group\EmbeddedGroup;

/**
 * The base class for embedded documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class EmbeddedDocument extends AbstractDocument
{
    /**
     * Set the root and path of the embedded document.
     *
     * @param \Mongator\Document\Document $root The root document.
     * @param string                      $path The path.
     *
     * @api
     */
    public function setRootAndPath(Document $root, $path)
    {
        $this->getArchive()->set('root_and_path', array('root' => $root, 'path' => $path));

        if (isset($this->data['embeddedsOne'])) {
            foreach ($this->data['embeddedsOne'] as $name => $embedded) {
                $embedded->setRootAndPath($root, $path.'.'.$name);
            }
        }

        if (isset($this->data['embeddedsMany'])) {
            foreach ($this->data['embeddedsMany'] as $name => $embedded) {
                $embedded->setRootAndPath($root, $path.'.'.$name);
            }
        }
    }

    /**
     * Returns the root and path of the embedded document.
     *
     * @return array An array with the root and path (root and path keys) or null if they do not exist.
     *
     * @api
     */
    public function getRootAndPath()
    {
        return $this->getArchive()->getOrDefault('root_and_path', null);
    }

    /**
     * Returns if the embedded document is an embedded one document changed.
     *
     * @return bool If the document is an embedded one document changed.
     */
    public function isEmbeddedOneChangedInParent()
    {
        if (!$rap = $this->getRootAndPath()) {
            return false;
        }

        if ($rap['root'] instanceof EmbeddedGroup) {
            return false;
        }

        $exPath = explode('.', $rap['path']);
        unset($exPath[count($exPath) -1 ]);

        $parentDocument = $rap['root'];
        foreach ($exPath as $embedded) {
            $parentDocument = $parentDocument->{'get'.ucfirst($embedded)}();
            if ($parentDocument instanceof EmbeddedGroup) {
                return false;
            }
        }

        $rap = $this->getRootAndPath();
        $exPath = explode('.', $rap['path']);
        $name = $exPath[count($exPath) - 1];

        return $parentDocument->isEmbeddedOneChanged($name);
    }

    /**
     * Returns whether the embedded document is an embedded many new.
     *
     * @return bool Whether the embedded document is an embedded many new.
     */
    public function isEmbeddedManyNew()
    {
        if (!$rap = $this->getRootAndPath()) {
            return false;
        }

        return false !== strpos($rap['path'], '._add');
    }

    /**
     * When a object is clonated whe mark all fields as modified.
     */
    public function __clone()
    {
        if (isset($this->data['fields'])) {
            foreach ($this->data['fields'] as $name => $value) {
                $this->fieldsModified[$name] = $value;
            }
        }
    }

    /**
     * {@inheritdoc }
     */
    public function isFieldInQuery($field)
    {
        $rap = $this->getRootAndPath();
        if (!$rap['root']) return false;
        return $rap['root']->isFieldInQuery($field);
    }

    /**
     * {@inheritdoc }
     */
    public function loadFull()
    {
        $rap = $this->getRootAndPath();
        if (!$rap['root']) return false;
        return $rap['root']->loadFull();
    }


}

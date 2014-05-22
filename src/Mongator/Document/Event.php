<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <mcuadros@gmail.com>
 * (c) Eduardo Gulias <me@egulias.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Document;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * The Event class for documents.
 *
 * @author Máximo Cuadros <mcuadros@gmail.com>
 * @author Eduardo Gulias <me@egulias.com>
 *
 * @api
 */ 
class Event extends BaseEvent
{
    private $document;

    public function __construct(AbstractDocument $document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }
}

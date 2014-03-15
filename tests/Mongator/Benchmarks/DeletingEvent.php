<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <mcuadros@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Benchmarks;

class DeletingEvent extends AthleticEvent
{
    public function setUp()
    {
        parent::setUp();
        $this->simpleDocument = $this->buildSimpleDocument()->save();
    }
    
    /**
     * @iterations 500
     */
    public function simpleDocument()
    {
        $this
            ->getRepository(self::SIMPLE_DOCUMENT_CLASS)
            ->delete($this->simpleDocument);
    }
}
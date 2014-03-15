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

use MongoClient;
use MongoId;

class UpdatingEvent extends AthleticEvent
{
    private $simpleDocument;

    protected function classSetUp()
    {
        parent::classSetUp();
        $this->simpleDocument = $this->buildSimpleDocument()->save();
        $this->complexDocument = $this->buildComplexDocument()->save();
    }

    /**
     * @iterations 500
     */
    public function simpleDocument()
    {
        $this->setBasicSimpleFields($this->simpleDocument);
        $this->simpleDocument->save();
    }

    /**
     * @iterations 500
     */
    public function simpleNestedDocument()
    {
        for ($i=0; $i < 50; $i++) { 
            $embeddeds[] = $this->buildSimpleEmbedded();
        }

        $this->simpleDocument->getNested()->replace($embeddeds);
        $this->simpleDocument->save();
    }

    /**
     * @iterations 500
     */
    public function complexDocument()
    {
        $this->setBasicComplexFields($this->complexDocument);
        $this->complexDocument->save();
    }

    /**
     * @iterations 500
     */
    public function complexNestedDocument()
    {
        for ($i=0; $i < 50; $i++) { 
            $embeddeds[] = $this->buildComplexEmbedded();
        }

        $this->complexDocument->getNested()->replace($embeddeds);
        $this->complexDocument->save();
    }
}
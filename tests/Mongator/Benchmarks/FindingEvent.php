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

use Exception;

class FindingEvent extends AthleticEvent
{
    protected $recordCount = 100;

    protected function classSetUp()
    {
        parent::classSetUp();

        for ($i=0; $i < $this->recordCount; $i++) { 
            $this->buildSimpleDocument()->setString(__CLASS__)->save();
            $this->buildSimpleNestedDocument()->setString(__CLASS__)->save();
            $this->buildComplexDocument()->setString(__CLASS__)->save();
            $this->buildComplexNestedDocument()->setString(__CLASS__)->save();
        }
    }

    /**
     * @iterations 100
     */
    public function simpleDocument()
    {
        $result = $this
            ->getRepository(self::SIMPLE_DOCUMENT_CLASS)
            ->createQuery(array(
                'nested' => array('$exists' => 0),
                'string' => __CLASS__
            ))
            ->all();

        $this->throwExceptionIfCountNotMatch($result);
    }

    /**
     * @iterations 100
     */
    public function simpleNestedDocument()
    {
        $result = $this
            ->getRepository(self::SIMPLE_DOCUMENT_CLASS)
            ->createQuery(array(
                'nested' => array('$exists' => 1),
                'string' => __CLASS__
            ))            ->all();
            
        $this->throwExceptionIfCountNotMatch($result);
    }

    /**
     * @iterations 100
     */
    public function complexDocument()
    {
        $result = $this
            ->getRepository(self::COMPLEX_DOCUMENT_CLASS)
            ->createQuery(array(
                'nested' => array('$exists' => 0),
                'string' => __CLASS__
            ))
            ->all();
            
        $this->throwExceptionIfCountNotMatch($result);
    }

    /**
     * @iterations 100
     */
    public function complexNestedDocument()
    {
        $result = $this
            ->getRepository(self::COMPLEX_DOCUMENT_CLASS)
            ->createQuery(array(
                'nested' => array('$exists' => 1),
                'string' => __CLASS__
            ))
            ->all();
            
        $this->throwExceptionIfCountNotMatch($result);
    }

    private function throwExceptionIfCountNotMatch(array $result)
    {

        if (count($result) != $this->recordCount) {
            throw new Exception(sprintf(
                'missmatch result find %d, expected %d records',
                count($result),
                $this->recordCount
            ));
        }
    }
}
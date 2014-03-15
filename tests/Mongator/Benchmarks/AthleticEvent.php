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

use Athletic\AthleticEvent as Base;
use Mongator\Connection;
use Mongator\Mongator;
use Mongator\Cache\ArrayCache;
use Mongator\Document\Document;
use DateTime;
use MongoId;
use MongoBinData;


abstract class AthleticEvent extends Base
{
    const SIMPLE_DOCUMENT_CLASS = 'Model\SimpleDocument';
    const SIMPLE_EMBEDDED_CLASS = 'Model\SimpleEmbedded';
    const COMPLEX_DOCUMENT_CLASS = 'Model\ComplexDocument';
    const COMPLEX_EMBEDDED_CLASS = 'Model\ComplexEmbedded';

    protected $metadataClass = 'Model\Mapping\Metadata';
    protected $server = 'mongodb://localhost:27017';
    protected $dbName = 'mongator_benchmarks';

    protected $connection;
    protected $globalConnection;
    protected $mongator;
    protected $unitOfWork;
    protected $metadataFactory;
    protected $cache;
    protected $mongo;
    protected $db;

    protected function classSetUp()
    {
        parent::classSetUp();
        $this->connection = new Connection($this->server, $this->dbName);
        $this->mongator = new Mongator(new $this->metadataClass);
        $this->mongator->setConnection('default', $this->connection);
        $this->mongator->setDefaultConnectionName('default');
        $this->mongator->setFieldsCache(new ArrayCache());
        $this->mongator->setDataCache(new ArrayCache());

        foreach ($this->mongator->getAllRepositories() as $repository) {
            $repository->getIdentityMap()->clear();
        }

        foreach ($this->connection->getMongoDB()->listCollections() as $collection) {
            $collection->deleteIndexes();
            $collection->drop();
        }
    }

    protected function setUp()
    {
        parent::setUp();
        foreach ($this->mongator->getAllRepositories() as $repository) {
            $repository->getIdentityMap()->clear();
        }
    }

    protected function getRepository($modelClass)
    {
        return $this->mongator->getRepository($modelClass);
    }

    protected function getCollection($modelClass)
    {
        return $this->getRepository($modelClass)->getCollection();
    }

    protected function create($modelClass)
    {
        return $this->mongator->create($modelClass);
    }

    protected function buildSimpleDocument(array $initial = [])
    {
        $document = $this->create(self::SIMPLE_DOCUMENT_CLASS);
        $this->setBasicSimpleFields($document);

        return $document;
    }

    protected function buildSimpleNestedDocument(array $initial = [])
    {
        $document = $this->create(self::SIMPLE_DOCUMENT_CLASS);
        $this->setBasicSimpleFields($document);

        for ($i=0; $i < 30; $i++) { 
            $embedded = $this->buildSimpleEmbedded();
            $document->addNested($embedded);
        }

        return $document;
    }

    protected function buildComplexDocument(array $initial = [])
    {
        $document = $this->create(self::COMPLEX_DOCUMENT_CLASS);
        $this->setBasicSimpleFields($document);
        $this->setBasicComplexFields($document);
     
        return $document;
    }

    protected function buildComplexNestedDocument(array $initial = [])
    {
        $document = $this->create(self::COMPLEX_DOCUMENT_CLASS);
        $this->setBasicSimpleFields($document);
        $this->setBasicComplexFields($document);

        for ($i=0; $i < 30; $i++) { 
            $embedded = $this->buildComplexEmbedded();
            $document->addNested($embedded);
        }

        return $document;
    }

    protected function buildSimpleEmbedded(array $initial = [])
    {
        $document = $this->create(self::SIMPLE_EMBEDDED_CLASS);
        $this->setBasicSimpleFields($document);

        return $document;
    }

    protected function buildComplexEmbedded(array $initial = [])
    {
        $document = $this->create(self::COMPLEX_EMBEDDED_CLASS);
        $this->setBasicSimpleFields($document);
        $this->setBasicComplexFields($document);

        return $document;
    }

    protected function setBasicComplexFields($document)
    {
        for($i=0; $i < 5; $i++) {
            $document->addReferencesMany($this->buildSimpleDocument());
        }

        $document->setReferencesOne($this->buildSimpleDocument());
        $document->setDate(new DateTime());
        $document->setBin(new MongoBinData('foo', 2));
    }

    protected function setBasicSimpleFields($document)
    {
        $document->setString('string');
        $document->setInt(32);
        $document->setFloat(32.32);
        $document->setField4((string) rand());
        $document->setField5((string) rand());
        $document->setField6((string) rand());
        $document->setField7((string) rand());
        $document->setField8((string) rand());
        $document->setField9((string) rand());
    }
}
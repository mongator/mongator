<?php

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;
use Mongator\Group\EmbeddedGroup;
use Mongator\Query\Query;

class QueryDefaultFindersTest extends TestCase
{
    private $repository;

    protected function setUp() {
        parent::setUp();
        $this->repository = $this->mongator->getRepository('Model\FieldTypeExamples');
    }

    public function testFindByFields() {
        $query = $this->createQuery()
            ->findByName('myname')
            ->findByPosition(3)
            ->findByIsActive(true)
        ;

        $this->assertSame(
            array(
                'name' => 'myname',
                'pos' => 3,
                'isActive' => true,
            ),
            $query->getCriteria()
        );
    }

    public function testDateCasting() {
        $date = new \DateTime();
        $mongoDate = new \MongoDate($date->getTimestamp());
        $expected = array('date' => $mongoDate);

        $query = $this->createQuery()->findByDate($mongoDate);
        $this->assertEquals($expected, $query->getCriteria());

        $query = $this->createQuery()->findByDate($date);
        $this->assertEquals($expected, $query->getCriteria());

        $query = $this->createQuery()->findByDate($date->getTimestamp());
        $this->assertEquals($expected, $query->getCriteria());
    }

    public function testIntTypecheck() {
        $this->setExpectedException('\Exception');
        $this->createQuery()->findByPosition('1');
    }

    public function testFloatTypecheck() {
        $this->setExpectedException('\Exception');
        $this->createQuery()->findByAvg('1');
    }

    public function testStringTypecheck() {
        $this->setExpectedException('\Exception');
        $this->createQuery()->findByName(33);
    }

    public function testDateTypecheck() {
        $this->setExpectedException('\Exception');
        $this->createQuery()->findByDate('2013-05-17');
    }

    public function testFindByReference() {
        $id = new \MongoId();
        $expected = array('author' => $id);

        $query = $this->createQuery()->findByAuthor($id);
        $this->assertEquals($expected, $query->getCriteria());

        $author = $this->mongator->create('Model\Author')->setId($id);
        $query = $this->createQuery()->findByAuthor($id);
        $this->assertEquals($expected, $query->getCriteria());
    }

    public function testFindByReferenceTypecheck() {
        $this->setExpectedException('\Exception');
        $this->createQuery()->findByAuthor((string)(new \MongoId()));
    }

    public function testIgnoreUnsearchableTypes() {
        $query = $this->createQuery();

        $this->assertFalse(method_exists($query, 'findByBindata'));
        $this->assertFalse(method_exists($query, 'findByRawdata'));
        $this->assertFalse(method_exists($query, 'findBySerializeddata'));
    }

    private function createQuery() {
        return $this->repository->createQuery();
    }
}


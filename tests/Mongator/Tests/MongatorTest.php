<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests;

use Mongator\Mongator;
use Mongator\Connection;
use Mongator\Cache\ArrayCache;

class MongatorTest extends TestCase
{
    public function testGetUnitOfWork()
    {
        $unitOfWork = $this->mongator->getUnitOfWork();
        $this->assertInstanceOf('Mongator\UnitOfWork', $unitOfWork);
        $this->assertSame($this->mongator, $unitOfWork->getMongator());
        $this->assertSame($unitOfWork, $this->mongator->getUnitOfWork());
    }

    public function testGetMetadataFactory()
    {
        $this->assertSame($this->metadataFactory, $this->mongator->getMetadataFactory());
    }

    public function testGetQueryFieldsCache()
    {
        $this->assertSame($this->cache, $this->mongator->getFieldsCache());
    }

    public function testGetLoggerCallable()
    {
        $loggerCallable = function() {};
        $mongator = new Mongator($this->metadataFactory, $loggerCallable);
        $this->assertSame($loggerCallable, $mongator->getLoggerCallable());
    }

    public function testConnections()
    {
        $connections = array(
            'local'  => new Connection('localhost', $this->dbName.'_local'),
            'global' => new Connection('localhost', $this->dbName.'_global'),
            'extra'  => new Connection('localhost', $this->dbName.'_extra'),
        );

        // hasConnection, setConnection, getConnection
        $mongator = new Mongator($this->metadataFactory);
        $this->assertFalse($mongator->hasConnection('local'));
        $mongator->setConnection('local', $connections['local']);
        $this->assertTrue($mongator->hasConnection('local'));
        $mongator->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mongator->getConnection('local'));
        $this->assertSame($connections['extra'], $mongator->getConnection('extra'));

        // setConnections, getConnections
        $mongator = new Mongator($this->metadataFactory);
        $mongator->setConnection('extra', $connections['extra']);
        $mongator->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mongator->getConnections());

        // removeConnection
        $mongator = new Mongator($this->metadataFactory);
        $mongator->setConnections($connections);
        $mongator->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mongator->getConnections());

        // clearConnections
        $mongator = new Mongator($this->metadataFactory);
        $mongator->setConnections($connections);
        $mongator->clearConnections();
        $this->assertSame(array(), $mongator->getConnections());

        // defaultConnection
        $mongator = new Mongator($this->metadataFactory);
        $mongator->setConnections($connections);
        $mongator->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mongator->getDefaultConnection());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mongator = new Mongator($this->metadataFactory);
        $mongator->getConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mongator = new Mongator($this->metadataFactory);
        $mongator->removeConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotDefaultConnectionName()
    {
        $mongator = new Mongator($this->metadataFactory);
        $mongator->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExist()
    {
        $mongator = new Mongator($this->metadataFactory);
        $mongator->setConnection('default', $this->connection);
        $mongator->getDefaultConnection();
    }

    public function testSetConnectionLoggerCallable()
    {
        $mongator = new Mongator($this->metadataFactory);
        $connection = new Connection($this->server, $this->dbName);
        $mongator->setConnection('default', $connection);
        $this->assertNull($connection->getLoggerCallable());
        $this->assertNull($connection->getLogDefault());

        $mongator = new Mongator($this->metadataFactory, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mongator->setConnection('default', $connection);
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
        $this->assertSame(array('connection' => 'default'), $connection->getLogDefault());
    }

    public function testDefaultConnectionName()
    {
        $mongator = new Mongator($this->metadataFactory);
        $this->assertNull($mongator->getDefaultConnectionName());
        $mongator->setDefaultConnectionName('Mongator_connection');
        $this->assertSame('Mongator_connection', $mongator->getDefaultConnectionName());
    }

    /**
     * @dataProvider getMetadataProvider
     */
    public function testGetMetadata($documentClass)
    {
        $metadataFactory = $this->getMock('Mongator\MetadataFactory');

        $metadataFactory
            ->expects($this->once())
            ->method('getClass')
            ->with($this->equalTo($documentClass))
        ;

        $mongator = new Mongator($metadataFactory);
        $mongator->getMetadata($documentClass);
    }

    public function getMetadataProvider()
    {
        return array(
            array('Model\Article'),
            array('Model\Author'),
        );
    }

    public function testCreate()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertInstanceOf('Model\Article', $article);

        $author = $this->mongator->create('Model\Author');
        $this->assertInstanceOf('Model\Author', $author);

        // defaults
        $book = $this->mongator->create('Model\Book');
        $this->assertSame('good', $book->getComment());
        $this->assertSame(true, $book->getIsHere());
    }

    public function testCreateInitializeArgs()
    {
        $author = $this->mongator->create('Model\Author');
        $initializeArgs = $this->mongator->create('Model\InitializeArgs', array($author));
        $this->assertSame($author, $initializeArgs->getAuthor());
    }

    public function testGetRepository()
    {
        $mongator = new Mongator($this->metadataFactory);

        $articleRepository = $mongator->getRepository('Model\Article');
        $this->assertInstanceOf('Model\ArticleRepository', $articleRepository);
        $this->assertSame($mongator, $articleRepository->getMongator());
        $this->assertSame($articleRepository, $mongator->getRepository('Model\Article'));

        $categoryRepository = $mongator->getRepository('Model\Category');
        $this->assertInstanceOf('Model\CategoryRepository', $categoryRepository);
    }

    public function testGetFieldCache()
    {
        $mongator = new Mongator($this->metadataFactory);

        $cache = new ArrayCache();
        $mongator->setFieldsCache($cache);
        $this->assertSame($cache, $mongator->getFieldsCache());
    }

    public function testGetDataCache()
    {
        $mongator = new Mongator($this->metadataFactory);

        $cache = new ArrayCache();
        $mongator->setDataCache($cache);
        $this->assertSame($cache, $mongator->getDataCache());
    }

    /**
     * @dataProvider fixMissingReferencesDataProvider
     */
    public function testFixAllMissingReferences($documentsPerBatch)
    {
        $author1 = $this->mongator->create('Model\Author')->setName('foo')->save();
        $author2 = $this->mongator->create('Model\Author')->setName('foo')->save();

        $category1 = $this->mongator->create('Model\Category')->setName('foo')->save();
        $category2 = $this->mongator->create('Model\Category')->setName('foo')->save();
        $category3 = $this->mongator->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->setAuthor($author1)->save();
        $article2 = $this->createArticle()->setAuthor($author2)->save();
        $article3 = $this->createArticle()->setAuthor($author1)->save();
        $article4 = $this->createArticle()->addCategories(array($category1, $category3))->save();
        $article5 = $this->createArticle()->addCategories($category2)->save();
        $article6 = $this->createArticle()->addCategories($category1)->save();

        $this->removeFromCollection($author1);
        $this->removeFromCollection($category1);

        $this->mongator->fixAllMissingReferences($documentsPerBatch);

        $this->assertFalse($this->documentExists($article1));
        $this->assertTrue($this->documentExists($article2));
        $this->assertFalse($this->documentExists($article3));

        $article4->refresh();
        $article5->refresh();
        $article6->refresh();

        $this->assertEquals(array($category3->getId()), $article4->getCategoryIds());
        $this->assertEquals(array($category2->getId()), $article5->getCategoryIds());
        $this->assertEquals(array(), $article6->getCategoryIds());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidEmbeddedDocumentClass()
    {
        $this->mongator->getRepository('Model\Source');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidOtherClass()
    {
        $this->mongator->getRepository('DateTime');
    }
}

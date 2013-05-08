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

use Mongator\DataLoader;
use Mongator\Mongator;

class DataLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $dataLoader = new DataLoader($this->mongator);
        $this->assertSame($this->mongator, $dataLoader->getMongator());
    }

    public function testSetGetMongator()
    {
        $dataLoader = new DataLoader($this->mongator);
        $dataLoader->setMongator($mongator = new Mongator($this->metadataFactory, $this->cache));
        $this->assertSame($mongator, $dataLoader->getMongator());
    }

    public function testLoad()
    {
        $data = array(
            'Model\Article' => array(
                'article_1' => array(
                    'title'   => 'Article 1',
                    'content' => 'Contuent',
                    'author'  => 'sormes',
                    'categories' => array(
                        'category_2',
                        'category_3',
                    ),
                ),
                'article_2' => array(
                    'title' => 'My Article 2',
                ),
            ),
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'PabloDip',
                ),
                'sormes' => array(
                    'name' => 'Francisco',
                ),
                'barbelith' => array(
                    'name' => 'Pedro',
                ),
            ),
            'Model\Category' => array(
                'category_1' => array(
                    'name' => 'Category1',
                ),
                'category_2' => array(
                    'name' => 'Category2',
                ),
                'category_3' => array(
                    'name' => 'Category3',
                ),
                'category_4' => array(
                    'name' => 'Category4',
                ),
            ),
        );

        $dataLoader = new DataLoader($this->mongator);
        $dataLoader->load($data);

        // articles
        $this->assertSame(2, $this->mongator->getRepository('Model\Article')->count());

        $article = $this->mongator->getRepository('Model\Article')->createQuery(array('title' => 'Article 1'))->one();
        $this->assertNotNull($article);
        $this->assertSame('Contuent', $article->getContent());
        $this->assertSame('Francisco', $article->getAuthor()->getName());
        $this->assertSame(2, count($article->getCategories()->getSaved()));

        $article = $this->mongator->getRepository('Model\Article')->createQuery(array('title' => 'My Article 2'))->one();
        $this->assertNotNull($article);
        $this->assertNull($article->getAuthorId());

        // authors
        $this->assertSame(3, $this->mongator->getRepository('Model\Author')->count());

        $author = $this->mongator->getRepository('Model\Author')->createQuery(array('name' => 'PabloDip'))->one();
        $this->assertNotNull($author);

        $author = $this->mongator->getRepository('Model\Author')->createQuery(array('name' => 'Francisco'))->one();
        $this->assertNotNull($author);

        $author = $this->mongator->getRepository('Model\Author')->createQuery(array('name' => 'Pedro'))->one();
        $this->assertNotNull($author);

        // categories
        $this->assertSame(4, $this->mongator->getRepository('Model\Category')->count());
    }

    public function testLoadSingleInheritanceReferences()
    {
        $data = array(
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'pablodip',
                ),
                'barbelith' => array(
                    'name' => 'barbelith',
                ),
            ),
            'Model\Category' => array(
                'mongodb' => array(
                    'name' => 'MongoDB',
                ),
                'php' => array(
                    'name' => 'PHP',
                ),
                'performance' => array(
                    'name' => 'Performance'
                ),
            ),
            'Model\RadioFormElement' => array(
                'radio_1' => array(
                    'author' => 'pablodip',
                    'categories' => array('mongodb', 'php'),
                ),
            ),
        );

        $dataLoader = new DataLoader($this->mongator);
        $dataLoader->load($data);

        $this->assertSame(1, $this->mongator->getRepository('Model\RadioFormElement')->createQuery()->count());
        $radio = $this->mongator->getRepository('Model\RadioFormElement')->createQuery()->one();
        $this->assertSame($this->mongator->getRepository('Model\Author')->createQuery(array('name' => 'pablodip'))->one(), $radio->getAuthor());
        $this->assertSame(2, count($radio->getCategories()->getSaved()));
    }

    public function testLoadPrune()
    {
        foreach ($this->mongator->getConnections() as $connection) {
            $connection->getMongoDB()->drop();
        }

        $data = array(
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'Pablo',
                ),
            ),
        );

        $dataLoader = new DataLoader($this->mongator);

        $dataLoader->load($data);
        $this->assertSame(1, $this->mongator->getRepository('Model\Author')->count());

        $dataLoader->load($data);
        $this->assertSame(2, $this->mongator->getRepository('Model\Author')->count());

        $dataLoader->load($data, false);
        $this->assertSame(3, $this->mongator->getRepository('Model\Author')->count());

        $dataLoader->load($data, true);
        $this->assertSame(1, $this->mongator->getRepository('Model\Author')->count());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadMongatorUnitOfWorkHasPending()
    {
        $author = $this->mongator->create('Model\Author');
        $this->mongator->persist($author);

        $dataLoader = new DataLoader($this->mongator);
        $dataLoader->load(array(
            'Model\Author' => array(
                'barbelith' => array(
                    'name' => 'Pedro',
                ),
            ),
        ));
    }
}

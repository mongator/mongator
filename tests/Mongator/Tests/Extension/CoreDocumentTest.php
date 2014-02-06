<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;
use Mongator\Group\EmbeddedGroup;

class CoreDocumentTest extends TestCase
{
    const EXAMPLE_MONGO_ID = '4af9f23d8ead0e1d32000000';
    public function testConstructorDefaultValues()
    {
        $book = $this->mongator->create('Model\Book');
        $book->setTitle('foo');
        $book->save();

        $this->assertSame('foo', $book->getTitle());
        $this->assertSame('good', $book->getComment());
        $this->assertSame(true, $book->getIsHere());
    }

    public function testFieldsSettersGetters()
    {
        $article = $this->mongator->create('Model\Article', array($this->mongator->create('Model\Author')));
        $this->assertNull($article->getTitle());
        $this->assertNull($article->getContent());
        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertSame($article, $article->setContent('bar'));
        $this->assertSame($article, $article->setNote(null));
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertNull($article->getNote());
    }

    public function testFieldsSetterQueryFieldIfItIsNotQueried()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertFalse($article->isFieldModified('title'));
    }

    public function testFieldsGettersQueryValueIfItDoesNotExistInNotNewDocuments()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('123', $article->getContent());
        $this->assertNull($article->getNote());
        $this->assertNull($article->getNote());
    }

    public function testFieldGetterSaveFieldsQueryCache()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $article->getTitle();
        $cache = $query->getFullCache();
        $this->assertSame(array('title' => 1), $cache['fields']);

        $article->getContent();
        $cache = $query->getFullCache();
        $this->assertSame(array('title' => 1, 'content' => 1), $cache['fields']);

        $article->getNote();
        $cache = $query->getFullCache();
        $this->assertSame(array('title' => 1, 'content' => 1, 'note' => 1), $cache['fields']);
    }

    public function testReferencesOneSettersGetters()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getAuthor());

        $author = $this->mongator->create('Model\Author');
        $this->assertSame($article, $article->setAuthor($author));
        $this->assertSame($author, $article->getAuthor());
        $this->assertNull($article->getAuthorId());

        $author = $this->mongator->create('Model\Author');
        $author->setId($id = new \MongoId(self::EXAMPLE_MONGO_ID));
        $author->setIsNew(false);
        $article->setAuthor($author);
        $this->assertSame($author, $article->getAuthor());
        $this->assertSame($id, $article->getAuthorId());

        $article->setAuthor(null);
        $this->assertNull($article->getAuthor());
        $this->assertNull($article->getAuthorId());
    }

    public function testReferencesOneGetterSaveReferenceCache()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['references']));

        $article->getAuthor();
        $cache = $query->getFullCache();
        $this->assertSame(array('author'), $cache['references']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReferencesOneSetterClassInvalid()
    {
        $this->mongator->create('Model\Article')->setAuthor($this->mongator->create('Model\Category'));
    }

    public function testReferencesOneGetterQuery()
    {
        $author = $this->mongator->create('Model\Author')->setName('foo')->save();

        $article = $this->mongator->create('Model\Article')->setAuthorId($author->getId());
        $this->assertSame($author, $article->getAuthor());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReferencesOneGetterQueryNotExist()
    {
        $article = $this->mongator->create('Model\Article')->setAuthorId(new \MongoId(self::EXAMPLE_MONGO_ID));
        $article->getAuthor();
    }

    public function testReferencesManyGetter()
    {
        $article = $this->mongator->create('Model\Article');
        $categories = $article->getCategories();
        $this->assertInstanceOf('Mongator\Group\ReferenceGroup', $categories);
        $this->assertSame($article, $categories->getParent());
        $this->assertSame('categoryIds', $categories->getField());
        $this->assertSame($categories, $article->getCategories());
    }

    public function testReferencesManyGetterSaveReferenceCache()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['references']));

        $article->getCategories();
        $cache = $query->getFullCache();
        $this->assertSame(array('categories'), $cache['references']);
    }

    public function testReferencesManyAdd()
    {
        $article = $this->mongator->create('Model\Article');
        $category = $this->mongator->create('Model\Category');
        $this->assertSame($article, $article->addCategories($category));
        $this->assertSame(array($category), $article->getCategories()->getAdd());
    }

    public function testReferencesManyRemove()
    {
        $article = $this->mongator->create('Model\Article');
        $category = $this->mongator->create('Model\Category');
        $this->assertSame($article, $article->removeCategories($category));
        $this->assertSame(array($category), $article->getCategories()->getRemove());
    }

    public function testUpdateReferenceFieldsReferencesOne()
    {
        $author = $this->mongator->create('Model\Author');
        $article = $this->mongator->create('Model\Article')->setAuthor($author);
        $author->setId(new \MongoId(self::EXAMPLE_MONGO_ID));
        $author->setIsNew(false);
        $article->updateReferenceFields();
        $this->assertSame($author->getId(), $article->getAuthorId());
    }

    public function testUpdateReferenceFieldsReferencesManyNew()
    {
        $article = $this->mongator->create('Model\Article');
        $categories = $article->getCategories();
        $ids = array();
        for ($i = 1; $i <= 5; $i ++) {
            $categories->add($this->mongator->create('Model\Category')->setId($ids[] = new \MongoId())->setIsNew(false));
        }
        $article->updateReferenceFields();
        $this->assertSame($ids, $article->getCategoryIds());
    }

    public function testUpdateReferenceFieldsReferencesManyNotNew()
    {
        foreach (range(1,4) as $value) $baseIds[] = new \MongoId();
        foreach (range(1,3) as $value) $sourceBaseIds[] = new \MongoId();
        foreach (range(1,3) as $value) $commentBaseIds[] = new \MongoId();


        $identityMap = $this->mongator->getRepository('Model\Category')->getIdentityMap();
        foreach (array($baseIds, $sourceBaseIds, $commentBaseIds) as $ids) {
            foreach ($ids as $id) {
                $category = $this->mongator->create('Model\Category');
                $category->setId($id);
                $category->setIsNew(false);
                $identityMap->set((string) $id, $category);
            }
        }

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'categories' => $baseIds,
            'source' => array(
                'categories' => $sourceBaseIds,
            ),
            'comments' => array(
                array(
                    'categories' => $commentBaseIds,
                ),
            ),
        ));
        $article->save();
        $categories = $article->getCategories();
        $addIds = array();
        for ($i = 1; $i <= 3; $i++) {
            $categories->add($this->mongator->create('Model\Category')->setId($addIds[] = new \MongoId())->setIsNew(false));
        }
        $categories->remove($this->mongator->create('Model\Category')->setId($baseIds[1])->setIsNew(false));
        $categories->remove($this->mongator->create('Model\Category')->setId($baseIds[3])->setIsNew(false));

        $categories = $article->getSource()->getCategories();
        $sourceAddIds = array();
        for ($i = 1; $i <= 2; $i++) {
            $categories->add($this->mongator->create('Model\Category')->setId($sourceAddIds[] = new \MongoId())->setIsNew(false));
        }
        $categories->remove($this->mongator->create('Model\Category')->setId($sourceBaseIds[1])->setIsNew(false));

        $comments = $article->getComments()->getSaved();
        $categories = $comments[0]->getCategories();
        $commentAddIds = array();
        for ($i = 1; $i <= 3; $i++) {
            $categories->add($this->mongator->create('Model\Category')->setId($commentAddIds[] = new \MongoId())->setIsNew(false));
        }
        $categories->remove($this->mongator->create('Model\Category')->setId($commentBaseIds[1])->setIsNew(false));

        $article->updateReferenceFields();
        $this->assertSame(array(
            $baseIds[0],
            $baseIds[2],
            $addIds[0],
            $addIds[1],
            $addIds[2],
        ), $article->getCategoryIds());
        $this->assertSame(array(
            $sourceBaseIds[0],
            $sourceBaseIds[2],
            $sourceAddIds[0],
            $sourceAddIds[1],
        ), $article->getSource()->getCategoryIds());
        $this->assertSame(array(
            $commentBaseIds[0],
            $commentBaseIds[2],
            $commentAddIds[0],
            $commentAddIds[1],
            $commentAddIds[2],
        ), $comments[0]->getCategoryIds());
    }

    public function testUpdateEreferenceFieldsShouldDoNothingWhenRemovingNotContainedDocuments()
    {
        $c1 = $this->createCategory('c1');
        $c2 = $this->createCategory('c2');
        $c3 = $this->createCategory('c3');

        $article = $this->createArticle()
            ->addCategories(array($c1, $c2))
            ->removeCategories($c3);

        $article->updateReferenceFields();

        $this->assertSame(array($c1->getId(), $c2->getId()), $article->getCategoryIds());
    }

    public function testSaveReferencesReferencesOne()
    {
        $article = $this->mongator->create('Model\Article');
        $author1 = $this->mongator->create('Model\Author')->setName('foo');
        $article->setAuthor($author1);
        $source = $this->mongator->create('Model\Source');
        $author2 = $this->mongator->create('Model\Author')->setName('bar');
        $source->setAuthor($author2);
        $article->setSource($source);
        $simpleEmbedded = $this->mongator->create('Model\SimpleEmbedded')->setName('foo');
        $article->setSimpleEmbedded($simpleEmbedded);

        $article->saveReferences();
        $this->assertFalse($author1->isModified());
        $this->assertFalse($author2->isModified());
    }

    public function testSaveReferencesReferencesMany()
    {
        $articleCategories = array(
            $this->mongator->create('Model\Category')->setName('c1')->save()->setName('c1u'),
            $this->mongator->create('Model\Category')->setName('c2')->save()->setName('c1u'),
            $this->mongator->create('Model\Category')->setName('c3'),
            $this->mongator->create('Model\Category')->setName('c4'),
        );

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'categories' => array($articleCategories[0]->getId(), $articleCategories[1]->getId()),
        ));
        $categories = $article->getCategories();
        $categories->getSaved();
        $categories->add(array($articleCategories[2], $articleCategories[3]));

        $article->saveReferences();

        foreach ($articleCategories as $category) {
            $this->assertFalse($category->isModified());
        }
    }

    public function testSaveReferencesWithCircularReference()
    {
        $one = $this->mongator->create('Model\CircularReference');
        $two = $this->mongator->create('Model\CircularReference');

        $one->setValue(1)->setOther($two);
        $two->setValue(2)->setOther($one);

        $one->save();

        $this->assertFalse($one->isNew() || $one->isModified());
        $this->assertFalse($two->isNew() || $two->isModified());
    }

    public function testEmbeddedsOneSettersGetters()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getSource());

        $source = $this->mongator->create('Model\Source');
        $this->assertSame($article, $article->setSource($source));
        $this->assertSame($source, $article->getSource());
        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());

        $source2 = $this->mongator->create('Model\Source');
        $article->setSource($source2);
        $this->assertSame($source2, $article->getSource());
        $this->assertSame(array('root' => $article, 'path' => 'source'), $source2->getRootAndPath());
    }

    public function testEmbeddedsOneSettersGettersDeep1()
    {
        $article = $this->mongator->create('Model\Article');
        $source = $this->mongator->create('Model\Source');
        $article->setSource($source);
        $info = $this->mongator->create('Model\Info');
        $source->setInfo($info);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testEmbeddedsOneSettersGettersDeep2()
    {
        $info = $this->mongator->create('Model\Info');
        $source = $this->mongator->create('Model\Source');
        $source->setInfo($info);
        $this->assertNull($info->getRootAndPath());
        $article = $this->mongator->create('Model\Article');
        $article->setSource($source);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testEmbeddedsOneGettersQueryValueIfItDoesNotExistInNotNewDocuments()
    {
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $source = $article->getSource();
        $this->assertNotNull($source);
        $this->assertSame('foo', $source->getName());
        $this->assertSame('234', $source->getText());
        $this->assertNull($source->getNote());
        $this->assertNull($source->getLine());
        $info = $source->getInfo();
        $this->assertNotNull($info);
        $this->assertSame('bar', $info->getText());
        $this->assertSame('345', $info->getLine());
        $this->assertNull($info->getName());
        $this->assertNull($info->getNote());
    }

    public function testEmbeddedsOneGetterNotQueryValueIfItIsAChangedEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mongator->create('Model\Article');
        $article->setId($articleRaw['_id']);
        $article->setIsNew(false);
        $source = $this->mongator->create('Model\Source');
        $article->setSource($source);
        $this->assertNull($source->getName());
        $this->assertNull($source->getText());
        $this->assertNull($source->getInfo());

        // deep
        $article = $this->mongator->create('Model\Article');
        $article->setId($articleRaw['_id']);
        $article->setIsNew(false);
        $source = $article->getSource();
        $info = $this->mongator->create('Model\Info');
        $source->setInfo($info);
        $this->assertNull($info->getText());
        $this->assertNull($info->getLine());
        $this->assertNull($info->getName());
    }

    public function testEmbeddedsOneGetterSaveFieldsCacheQuering()
    {
/*
        $this->markTestSkipped(
              'Pending to review change on commit 00d38267e8eb61786c8543d20cfd1f33c9e65c15.'
        );
*/
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $source = $article->getSource();
        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $source->getName();
        $cache = $query->getFullCache();
        $this->assertSame(array('source.name' => 1), $cache['fields']);

        $source->getText();
        $cache = $query->getFullCache();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1), $cache['fields']);

        $source->getNote();
        $cache = $query->getFullCache();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1, 'source.note' => 1), $cache['fields']);

        $info = $source->getInfo();
        $cache = $query->getFullCache();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1, 'source.note' => 1), $cache['fields']);

        $info->getName();
        $cache = $query->getFullCache();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
        ), $cache['fields']);

        $info->getNote();
        $cache = $query->getFullCache();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
            'source.info.note' => 1,
        ), $cache['fields']);

        $info->getLine();
        $cache = $query->getFullCache();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
            'source.info.note' => 1,
            'source.info.line' => 1,
        ), $cache['fields']);
    }

    public function testEmbeddedsManyGetter()
    {
        $article = $this->mongator->create('Model\Article');
        $comments = $article->getComments();
        $this->assertInstanceOf('Mongator\Group\EmbeddedGroup', $comments);
        $this->assertSame('Model\Comment', $comments->getDocumentClass());
        $this->assertSame($comments, $article->getComments());

        $comment = $this->mongator->create('Model\Comment');
        $comments->add($comment);
        $infos = $comment->getInfos();
        $this->assertInstanceOf('Mongator\Group\EmbeddedGroup', $infos);
        $this->assertSame('Model\Info', $infos->getDocumentClass());
        $this->assertSame($infos, $comment->getInfos());
    }

    public function testEmbeddedsManyAdd()
    {
        $article = $this->mongator->create('Model\Article');
        $comment = $this->mongator->create('Model\Comment');
        $this->assertSame($article, $article->addComments($comment));
        $this->assertSame(array($comment), $article->getComments()->getAdd());
    }

    public function testEmbeddedsManyRemove()
    {
        $article = $this->mongator->create('Model\Article');
        $comment = $this->mongator->create('Model\Comment');
        $this->assertSame($article, $article->removeComments($comment));
        $this->assertSame(array($comment), $article->getComments()->getRemove());
    }

    public function testRelationsOne()
    {
        $information = $this->mongator->create('Model\ArticleInformation')->setName('foo')->save();
        $article = $this->mongator->create('Model\Article')->setInformation($information)->save();
        $this->assertSame($article, $information->getArticle());

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $this->assertEquals($article->getId(), $information->getArticle()->getId());
    }

    public function testRelationsManyOne()
    {
        $authors = array();
        for ($i = 0; $i < 10; $i++) {
            $authors[] = $this->mongator->create('Model\Author')->setName('Author'.$i)->save();
        }
        $articles = array();
        for ($i = 0; $i < 10; $i++) {
            $articles[] = $this->mongator->create('Model\Article')->setTitle('Article'.$i)->save();
        }

        $query = $authors[3]->getArticles();
        $this->assertInstanceOf('Mongator\Query\Query', $query);
        $this->assertSame(array('author' => $authors[3]->getId()), $query->getCriteria());
        $this->assertSame(0, $query->count());
        $this->assertSame(0, $authors[5]->getArticles()->count());

        $articles[2]->setAuthor($authors[3])->save();
        $articles[3]->setAuthor($authors[3])->save();
        $articles[5]->setAuthor($authors[6])->save();

        $this->assertSame(array(
            $articles[2]->getId()->__toString() => $articles[2],
            $articles[3]->getId()->__toString() => $articles[3],
        ), $authors[3]->getArticles()->all());
        $this->assertSame(array(
            $articles[5]->getId()->__toString() => $articles[5],
        ), $authors[6]->getArticles()->all());
        $this->assertSame(0, $authors[5]->getArticles()->count());
    }

    public function testRelationsManyMany()
    {
        $categories = array();
        for ($i = 0; $i < 10; $i++) {
            $categories[] = $this->mongator->create('Model\Category')->setName('Category'.$i)->save();
        }
        $articles = array();
        for ($i = 0; $i < 10; $i++) {
            $articles[] = $this->mongator->create('Model\Article')->setTitle('Article'.$i)->save();
        }

        $query = $categories[3]->getArticles();
        $this->assertInstanceOf('Mongator\Query\Query', $query);
        $this->assertSame(array('categories' => $categories[3]->getId()), $query->getCriteria());
        $this->assertSame(0, $query->count());
        $this->assertSame(0, $categories[5]->getArticles()->count());

        $articles[2]->getCategories()->add($categories[3]);
        $articles[2]->getCategories()->add($categories[4]);
        $articles[2]->save();
        $articles[3]->getCategories()->add($categories[3]);
        $articles[3]->save();
        $articles[5]->getCategories()->add($categories[6]);
        $articles[5]->save();

        $this->assertSame(array(
            $articles[2]->getId()->__toString() => $articles[2],
            $articles[3]->getId()->__toString() => $articles[3],
        ), $categories[3]->getArticles()->all());
        $this->assertSame(array(
            $articles[5]->getId()->__toString() => $articles[5],
        ), $categories[6]->getArticles()->all());
        $this->assertSame(0, $categories[5]->getArticles()->count());
    }

    public function testRelationsManyThrough()
    {
        $users = array();
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $user = $this->mongator->create('Model\User');
            $user->setUsername('user'.$i);
            $this->mongator->persist($user);
        }
        $articles = array();
        $articlesVotes = array();
        for ($i = 1; $i <= 10; $i++) {
            $articles[$i] = $article = $this->mongator->create('Model\Article');
            $article->setTitle('article'.$i);
            $this->mongator->persist($article);

            for ($z = $i; $z <= 10; $z++) {
                $articleVote = $this->mongator->create('Model\ArticleVote');
                $articleVote->setArticle($article);
                $articleVote->setUser($users[$z]);
                $this->mongator->persist($articleVote);

                $articlesVotes[$i][] = $articleVote->getUser();
            }
        }
        $this->mongator->flush();

        $ids = array();
        foreach ($articlesVotes[5] as $articleVote) {
            $ids[] = $articleVote->getId();
        }
        $query = $this->mongator->getRepository('Model\ArticleVote')->createQuery(array('_id' => array('$in' => $ids)));
        $this->assertEquals($query->getCriteria(), $articles[5]->getVotesUsers()->getCriteria());
    }

    public function testResetGroups()
    {
        $article = $this->mongator->create('Model\Article')
            // referencesMany
            ->addCategories($this->mongator->create('Model\Category'))
            // embeddedsMany
            ->addComments($this->mongator->create('Model\Comment'))
            // embeddedsOne with groups
            ->setSource($this->mongator->create('Model\Source')
                ->addCategories($this->mongator->create('Model\Category'))
            )
            // embeddedsMany with groups
            ->addComments($comment = $this->mongator->create('Model\Comment')
                ->addInfos($this->mongator->create('Model\Info'))
            )
        ;
        $article->resetGroups();

        $this->assertSame(0, count($article->getCategories()));
        $this->assertSame(0, count($article->getComments()));
        $this->assertSame(0, count($article->getSource()->getCategories()));
        $this->assertSame(0, count($comment->getInfos()));
    }

    public function testSetMethod()
    {
        $article = $this->mongator->create('Model\Article');

        // fields
        $this->assertSame($article, $article->set('title', 'foo'));;
        $this->assertSame('foo', $article->getTitle());

        // references one
        $author = $this->mongator->create('Model\Author');
        $this->assertSame($article, $article->set('author', $author));
        $this->assertSame($author, $article->getAuthor());

        // embeddeds one
        $source = $this->mongator->create('Model\Source');
        $this->assertSame($article, $article->set('source', $source));
        $this->assertSame($source, $article->getSource());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetMethodInvalidDataName()
    {
        $this->mongator->create('Model\Article')->set('no', 'foo');
    }

    public function testGetMethod()
    {
        $article = $this->mongator->create('Model\Article');

        // fields
        $article->setTitle('bar');
        $this->assertSame('bar', $article->get('title'));

        // references one
        $author = $this->mongator->create('Model\Author');
        $article->setAuthor($author);
        $this->assertSame($author, $article->get('author'));

        // references many
        $this->assertSame($article->getCategories(), $article->get('categories'));

        // embeddeds one
        $source = $this->mongator->create('Model\Source');
        $article->setSource($source);
        $this->assertSame($source, $article->get('source'));

        // embeddeds many
        $this->assertSame($article->getComments(), $article->get('comments'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetMethodInvalidDataName()
    {
        $this->mongator->create('Model\Article')->get('no');
    }

    public function testFromArray()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertSame($article, $article->fromArray(array(
            'id'       => 123,
            'title'    => 'foo',
            'content'  => 'bar',
            'isActive' => true,
            'source' => array(
                'name' => 'foobar',
                'text' => 'barfoo',
                'info' => array(
                    'text' => 'fooups',
                    'line' => 'upsfoo',
                ),
            ),
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'upsfoo',
                            'text' => 'fooups',
                        ),
                        array(
                            'name' => 'mongo',
                            'text' => 'db',
                        ),
                    ),
                ),
                array(
                    'text' => 'foobar',
                    'note' => 'barfoo',
                ),
            ),
        )));

        // id
        $this->assertSame(123, $article->getId());

        // fields
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertTrue($article->getIsActive());

        // embeddeds one
        $this->assertNotNull($article->getSource());
        $source = $article->getSource();
        $this->assertSame('foobar', $source->getName());
        $this->assertSame('barfoo', $source->getText());
        $this->assertNotNull($source->getInfo());
        $info = $source->getInfo();
        $this->assertSame('fooups', $info->getText());
        $this->assertSame('upsfoo', $info->getLine());

        // embeddeds many
        $comments = $article->getComments()->getSaved();
        $this->assertSame(0, count($comments));
        $commentsAdd = $article->getComments()->getAdd();
        $this->assertSame('foo', $commentsAdd[0]->getName());
        $this->assertSame('bar', $commentsAdd[0]->getText());
        $infos = $commentsAdd[0]->getInfos()->getSaved();
        $this->assertSame(2, count($infos));
        $infosAdd = $commentsAdd[0]->getInfos()->getAdd();
    }

    public function testFromArrayIgnoresIdInEmbeddedDocuments()
    {
        $source = $this->mongator->create('Model\Source');
        $source->fromArray(array(
            'id'   => 2,
            'name' => 'foo',
        ));

        $this->assertSame('foo', $source->getName());
    }

    public function testFromArrayReferencesOne()
    {
        $author = $this->mongator->create('Model\Author');
        $article = $this->mongator->create('Model\Article');
        $article->fromArray(array('author' => $author));

        $this->assertSame($author, $article->getAuthor());
    }

    public function testFromArrayReferencesMany()
    {
        $categories = array(
            $this->mongator->create('Model\Category'),
            $this->mongator->create('Model\Category'),
        );
        $article = $this->mongator->create('Model\Article');
        $article->fromArray(array('categories' => $categories));

        $this->assertSame($categories, $article->getCategories()->all());
    }

    public function testFromArrayReferencesManyRemoveCurrent()
    {
        $category1 = $this->mongator->create('Model\Category');
        $category2 = $this->mongator->create('Model\Category');

        $article = $this->mongator->create('Model\Article');
        $article->addCategories($category1);
        $article->fromArray(array('categories' => array($category2)));

        $this->assertSame(array($category2), $article->getCategories()->all());
    }

    public function testToArray()
    {
        $article = $this->mongator->create('Model\Article')
            ->setId(123)
            ->setTitle('foo')
            ->setContent('bar')
            ->setNote(null)
            ->setIsActive(false)
        ;
        $this->assertSame(array(
            'id'       => 123,
            'title'    => 'foo',
            'content'  => 'bar',
            'note'     => null,
            'line'     => null,
            'text'     => null,
            'isActive' => false,
            'date'     => null,
            'database' => null,
        ), $article->toArray());
    }

    public function testToArrayInitializeFields()
    {
        $article = $this->mongator->create('Model\Article')
            ->setTitle('foo')
            ->setContent('bar')
            ->setIsActive(false)
            ->save()
        ;

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($article->getId());

        $this->assertSame(array(
            'id'       => $article->getId(),
            'title'    => 'foo',
            'content'  => 'bar',
            'note'     => null,
            'line'     => null,
            'text'     => null,
            'isActive' => false,
            'date'     => null,
            'database' => null,
        ), $article->toArray());
    }

    public function testToArrayIgnoresIdInEmbeddedDocuments()
    {
        $source = $this->mongator->create('Model\Source');
        $source->setName('foo');

        $this->assertSame(array(
            'name' => 'foo',
            'text' => null,
            'note' => null,
            'line' => null,
            'from' => null
        ), $source->toArray());
    }

    /*
     * Related to Mongator\Group\ReferenceGroup
     */
    public function testReferencesManyQuery()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 10; $i++) {
            $category = $this->mongator->create('Model\Category')->setName('Category'.$i)->save();
            if ($i % 2) {
                $categories[$category->getId()->__toString()] = $category;
                $ids[] = $category->getId();
            }
        }

        $article = $this->mongator->create('Model\Article')->setCategoryIds($ids);
        $this->assertSame($categories, $article->getCategories()->getSaved());

        $query = $article->getCategories()->createQuery();
        $this->assertInstanceOf('Model\CategoryQuery', $query);
        $this->assertSame(array('_id' => array('$in' => $ids)), $query->getCriteria());
    }

    /*
     * Related to Mongator\Group\EmbeddedGroup
     */

    public function testEmbeddedsManyQueryValueIfItDoesNotExistInNotNewDocument()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $comments = $article->getComments()->getSaved();

        $this->assertSame(2, count($comments));
        $this->assertSame('foo', $comments[0]->getName());
        $this->assertSame('bar', $comments[0]->getText());
        $this->assertNull($comments[0]->getNote());
        $infos = $comments[0]->getInfos()->getSaved();
        $this->assertSame(2, count($infos));
        $this->assertSame('foobar', $infos[0]->getName());
        $this->assertNull($infos[0]->getText());
        $this->assertSame('barfoo', $infos[0]->getLine());
        $this->assertSame(array(), $comments[1]->getInfos()->getSaved());
    }

    public function testEmbeddedsManySaveFieldsCacheQuering()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $comments = $article->getComments();
        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $savedComments = $comments->getSaved();
        $cache = $query->getFullCache();
        $this->assertSame(array('comments' => 1), $cache['fields']);
        foreach ($comments as $comment) {
            $comment->getName();
        }

        $cache = $query->getFullCache();
        $this->assertSame(array('comments' => 1, 'comments.name' => 1), $cache['fields']);
        $commentNew = $this->mongator->create('Model\Comment');
        $comments->add($commentNew);
        $commentNew->getName();

        $cache = $query->getFullCache();
        $this->assertSame(array('comments' => 1, 'comments.name' => 1), $cache['fields']);
        $savedInfos = $savedComments[0]->getInfos()->getSaved();

        $cache = $query->getFullCache();
        $this->assertSame(array('comments' => 1, 'comments.name' => 1, 'comments.infos' => 1), $cache['fields']);
    }

    public function testEmbeddedsManyNoQueryNewDocument()
    {
        $this->assertSame(array(), $this->mongator->create('Model\Article')->getComments()->getSaved());
    }

    public function testEmbeddedsManyCount()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
                array(
                    'name' => 'foo3',
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $this->assertSame(3, $article->getComments()->count());
    }

    /*
     * Related to Mongator\Document\Document
     */

    public function testRepository()
    {
        $this->assertSame($this->mongator->getRepository('Model\Article'), $this->mongator->getRepository('Model\Article'));
        $this->assertSame($this->mongator->getRepository('Model\Category'), $this->mongator->getRepository('Model\Category'));
    }

    public function testRefresh()
    {
        $articleRaw = array(
            'title'    => 'foo',
            'content'  => 'bar',
            'isActive' => 1,
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mongator->create('Model\Article')->setId($articleRaw['_id']);
        $article->setIsNew(false);
        $article->setTitle('ups')->setNote('bump');
        $article->refresh();
        $this->assertFalse($article->isModified());
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertNull($article->getNote());
        $this->assertTrue($article->getIsActive());
    }

    /*
     * Related to Mongator\Document\AbstractDocument
     */

    public function testGetMetadata()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertSame($this->mongator->getMetadataFactory()->getClass('Model\Article'), $article->getMetadata());
    }

    public function testRootAndPath()
    {
        $article1 = $this->mongator->create('Model\Article');
        $article2 = $this->mongator->create('Model\Article');

        $source = $this->mongator->create('Model\Source');
        $this->assertNull($source->getRootAndPath());
        $source->setRootAndPath($article1, 'source');
        $this->assertSame(array('root' => $article1, 'path' => 'source'), $source->getRootAndPath());
        $source->setRootAndPath($article2, 'fuente');
        $this->assertSame(array('root' => $article2, 'path' => 'fuente'), $source->getRootAndPath());
    }

    public function testRootAndPathEmbeddedsOne()
    {
        $info = $this->mongator->create('Model\Info');
        $source = $this->mongator->create('Model\Source');
        $source->setInfo($info);
        $article = $this->mongator->create('Model\Article');
        $article->setSource($source);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testRootAndPathEmbeddedsMany()
    {
        $info = $this->mongator->create('Model\Info');
        $comment = $this->mongator->create('Model\Comment');
        $comment->getInfos()->add($info);
        $article = $this->mongator->create('Model\Article');
        $article->getComments()->add($comment);

        $this->assertSame(array('root' => $article, 'path' => 'comments'), $article->getComments()->getRootAndPath());
        $rap = $comment->getRootAndPath();
        $this->assertSame($article, $rap['root']);
        $this->assertSame('comments._add0', $rap['path']);
        $rap = $info->getRootAndPath();
        $this->assertSame($article, $rap['root']);
        $this->assertSame('comments._add0.infos._add0', $rap['path']);
    }

    public function testDebug()
    {
        $article = $this->mongator->create('Model\Article');

        $this->assertTrue(is_array($article->debug()));
    }

    /*
     * setDocumentData
     */
    public function testSetDocumentData()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertSame($article, $article->setDocumentData(array(
            '_id'         => $id = new \MongoId(self::EXAMPLE_MONGO_ID),
            '_query_hash' => $queryHash = md5(1),
            'title'       => 'foo',
            'isActive'    => 1,
        )));

        $this->assertSame($id, $article->getId());
        $this->assertSame(array($queryHash), $article->getQueryHashes());
        $this->assertSame('foo', $article->getTitle());
        $this->assertTrue($article->getIsActive());
    }

    public function testSetDocumentDataCleaning()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->setDocumentData(array(
            '_id'   => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title' => 'foo',
        ), true);
        $this->assertFalse($article->isFieldModified('title'));
    }

    public function testSetDocumentDataDefaultValues()
    {
        $book = $this->mongator->create('Model\Book');
        $book->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
        ));
        $this->assertFalse($book->isFieldModified('comment'));
        $this->assertFalse($book->isFieldModified('isHere'));
    }

    public function testSetDocumentDataEmbeddedsOne()
    {
        $infoData = array('name' => 234);
        $sourceData = array('name' => 123, 'info' => $infoData);
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_query_hash' => $queryHash = md5('mongo'),
            'source'      => $sourceData,
        ));

        $source = $article->getSource();
        $source->setRootAndPath($article, 'source');

        $expected = $this->mongator->create('Model\Source')->setDocumentData($sourceData);;
        $expected->setRootAndPath($article, 'source');
        
        $this->assertSameGroups($expected, $source);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        
        $info = $source->getInfo();
        $expected = $this->mongator->create('Model\Info')->setDocumentData($infoData);
        $expected->setRootAndPath($article, 'source.info');

        $this->assertEquals($expected, $info);
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function assertSameGroups($groupA, $groupB)
    {
        if (!$groupA instanceOf Mongator\Group\AbstractGroup) {
            $a = array($groupA);
            $b = array($groupB);
        } else {
            $a = $groupA->all();
            $b = $groupB->all(); 
        }

        foreach ($a as $key => $document) {
           // $a[$key]->getArchive()->remove('saved_data');
           // $b[$key]->getArchive()->remove('saved_data');

            $this->assertEquals($a[$key], $b[$key]);
        }
    }

    public function testSetDocumentDataEmbeddedsMany()
    {
         $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $infosData = array(
            array('name' => 'foo', 'text' => 'foobar'),
            array('name' => 'bar', 'text' => 'barfoo'),
        );
        $commentsData = array(
            array('name' => 'ups', 'text' => 'upsfoo'),
            array('text' => 'mon', 'line' => 'monfoo', 'infos' => $infosData),
        );
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_query_hash' => $queryHash = md5('mongodb'),
            'comments'    => $commentsData,
        ));

        $comments = $article->getComments();
        $expected = new EmbeddedGroup('Model\Comment', $commentsData);
        $expected->setRootAndPath($article, 'comments');

        $savedComments = $comments->getSaved();
        $this->assertSame(2, count($savedComments));

        $this->assertSameGroups($this->mongator->create('Model\Comment')->setDocumentData($commentsData[0])->getName(), $savedComments[0]->getName());
        $this->assertSameGroups($this->mongator->create('Model\Comment')->setDocumentData($commentsData[1])->getName(), $savedComments[1]->getName());

        $this->assertSameGroups($expected, $comments);
        $this->assertSame(array('root' => $article, 'path' => 'comments'), $comments->getRootAndPath());  

        $this->assertSame(0, $savedComments[0]->getInfos()->count());
        $infos = $savedComments[1]->getInfos();
        $this->assertEquals(new EmbeddedGroup('Model\Info', $infosData), $infos);
        $this->assertSame(array('root' => $article, 'path' => 'comments.1.infos'), $infos->getRootAndPath());
        $savedInfos = $infos->getSaved();
        $this->assertSame(2, count($savedComments));
        $this->assertEquals($this->mongator->create('Model\Info')->setDocumentData($infosData[0]), $savedInfos[0]);
        $this->assertEquals($this->mongator->create('Model\Info')->setDocumentData($infosData[1]), $savedInfos[1]);
    }

    /*
     * isModified
     */
    public function testIsModifiedNewNotModified()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertFalse($article->isModified());
    }

    public function testIsModifiedNewFieldsModified()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setLine('bar');
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNewFieldsDefaultValues()
    {
        $book = $this->mongator->create('Model\Book');
        $this->assertFalse($book->isModified());
    }

    public function testIsModifiedNotNewFieldsNotModified()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'      => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'    => 'bar',
            'content'  => 'foo',
            'isActive' => true,
        ));
        $this->assertFalse($article->isModified());
    }

    public function testIsModifiedNotNewFieldsModified()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'      => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'    => 'bar',
            'content'  => 'foo',
            'isActive' => true,
        ));
        $article->setContent('ups');
        $this->assertTrue($article->isModified());

        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'      => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'    => 'bar',
            'content'  => 'foo',
            'isActive' => true,
        ));
        $article->setLine('ups');
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNotNewFieldsDefaultValues()
    {
        $book = $this->mongator->create('Model\Book');
        $book->setDocumentData(array(
            '_id'   => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title' => 'foo',
        ));
        $this->assertFalse($book->isModified());
    }

    public function testIsModifiedNewEmbeddedsOne()
    {
        $article = $this->mongator->create('Model\Article');
        $source = $this->mongator->create('Model\Source');
        $article->setSource($source);
        $this->assertFalse($article->isModified());
        $source->setName('foo');
        $this->assertTrue($article->isModified());
        $article->setSource(null);
        $this->assertFalse($article->isModified());

        $info = $this->mongator->create('Model\Info');
        $source = $this->mongator->create('Model\Source')->setInfo($info);
        $article = $this->mongator->create('Model\Article')->setSource($source);
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());
        $info->setName('bar');
        $this->assertTrue($source->isModified());
        $this->assertTrue($source->isModified());
    }

    public function testIsModifiedNotNewEmbeddedsOne()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'source' => array('name' => 'foo'),
        ));
        $this->assertFalse($article->isModified());
        $source = $article->getSource();
        $source->setName('bar');
        $this->assertTrue($article->isModified());
        $source->setName('foo');
        $this->assertFalse($article->isModified());
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertTrue($article->isModified());
        $article->setSource(null);
        $this->assertTrue($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'source' => array('info' => array('name' => 'foo')),
        ));
        $source = $article->getSource();
        $this->assertFalse($source->isModified());
        $info = $source->getInfo();
        $info->setName('bar');
        $this->assertTrue($source->isModified());
        $this->assertTrue($article->isModified());
        $info->setName('foo');
        $this->assertFalse($info->isModified());
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertTrue($source->isModified());
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNewEmbeddedsMany()
    {
        $article = $this->mongator->create('Model\Article');
        $comments = $article->getComments();
        $this->assertFalse($article->isModified());
        $comments->remove($this->mongator->create('Model\Comment')->setName('foo'));
        $this->assertFalse($article->isModified());
        $comment = $this->mongator->create('Model\Comment');
        $comments->add($comment);
        // TODO: Decide whether adding an empty embedded should be considered a modification
        $this->assertTrue($article->isModified());
        $comment->setName('bar');
        $this->assertTrue($article->isModified());

        $article = $this->mongator->create('Model\Article');
        $comments = $article->getComments();
        $comment = $this->mongator->create('Model\Comment');
        $comments->add($comment);
        $infos = $comment->getInfos();
        $this->assertFalse($comment->isModified());
        $this->assertFalse($article->isModified());
        $infos->remove($this->mongator->create('Model\Info')->setName('foo'));
        $this->assertFalse($comment->isModified());
        $this->assertFalse($article->isModified());
        $info = $this->mongator->create('Model\Info');
        $infos->add($info);
        // TODO: Decide whether adding an empty embedded should be considered a modification
        $this->assertTrue($comment->isModified());
        $this->assertTrue($article->isModified());
        $info->setName('bar');
        $this->assertTrue($comment->isModified());
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNotNewEmbeddedsMany()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $this->assertFalse($article->isModified());
        $savedComments = $comments->getSaved();
        $this->assertFalse($article->isModified());
        // add
        $addComment = $this->mongator->create('Model\Comment');
        $comments->add($addComment);
        // TODO: Decide whether adding an empty embedded should be considered a modification
        $this->assertTrue($article->isModified());
        $addComment->setName('foobar');
        $this->assertTrue($article->isModified());
        $comments->clearAdd();
        $this->assertFalse($article->isModified());
        // remove
        $comments->remove($savedComments[0]);
        $this->assertTrue($article->isModified());
        $comments->clearRemove();
        $this->assertFalse($article->isModified());
        // edit
        $savedComments[1]->setName('foobar');
        $this->assertTrue($article->isModified());
        $savedComments[1]->setName('bar');
        $this->assertFalse($article->isModified());
    }

    /*
     * clearModified
     */
    public function testClearModifiedFields()
    {
        $article = $this->mongator->create('Model\Article')->setTitle('foo');
        $article->clearModified();
        $this->assertSame('foo', $article->getTitle());
        $this->assertFalse($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array('_id' => new \MongoId(), 'title' => 'foo'))->setTitle('bar');
        $article->clearModified();
        $this->assertSame('bar', $article->getTitle());
        $this->assertFalse($article->isModified());
    }

    public function testClearModifiedEmbeddedsOne()
    {
        $source = $this->mongator->create('Model\Source')->setName('foo');
        $article = $this->mongator->create('Model\Article')->setSource($source);
        $article->clearModified();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertSame($source, $article->getSource());
        $this->assertSame('foo', $source->getName());
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array('_id' => new \MongoId(), 'source' => array('name' => 'foo')));
        $source = $article->getSource();
        $source->setName('bar');
        $article->setSource(null);
        $article->clearModified();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertNull($article->getSource());
        $this->assertTrue($source->isModified());
        $this->assertFalse($article->isModified());
    }

    public function testClearModifiedEmbeddedsMany()
    {
        $article = $this->mongator->create('Model\Article');
        $comments = $article->getComments();
        $comment = $this->mongator->create('Model\Comment')->setName('foo');
        $article->clearModified();
        $this->assertSame(array(), $comments->getAdd());
        $this->assertTrue($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->getSaved();
        $comments->add($this->mongator->create('Model\Comment')->setName('foobar'));
        $article->clearModified();
        $this->assertSame(array(), $comments->getAdd());
        $this->assertTrue($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->getSaved();
        $comments->remove($savedComments[0]);
        $article->clearModified();
        $this->assertSame(array(), $comments->getRemove());
        $this->assertTrue($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->getSaved();
        $savedComments[1]->setName('foobar');
        $article->clearModified();
        $this->assertTrue($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());
    }

    /*
     * isFieldModified
     */
    public function testIsFieldModifiedWithoutValue()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        $source = $this->mongator->create('Model\Source');
        $this->assertFalse($source->isFieldModified('name'));
        $this->assertFalse($source->isFieldModified('text'));
    }

    public function testIsFieldModifiedChange()
    {
        $article = $this->mongator->create('Model\Article')->setTitle('foo')->setNote(null);
        $this->assertTrue($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));
        $this->assertFalse($article->isFieldModified('note'));

        $source = $this->mongator->create('Model\Source')->setName('foo')->setNote(null);
        $this->assertTrue($source->isFieldModified('name'));
        $this->assertFalse($source->isFieldModified('text'));
        $this->assertFalse($source->isFieldModified('note'));
    }

    public function testIsFieldModifiedHydrate()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'     => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'   => 'foo',
            'content' => 'bar',
            'note'    => 'foobar',
        ));
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        // change other
        $article->setTitle('ups');
        $this->assertTrue($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        // change same
        $article->setTitle('foo');
        $this->assertFalse($article->isFieldModified('title'));

        // change same directly
        $article->setNote('foobar');
        $this->assertFalse($article->isFieldModified('note'));
    }

    /*
     * getOriginalFieldValue
     */
    public function testGetOriginalFieldValueFieldsWithoutValue()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getOriginalFieldValue('title'));
        $this->assertNull($article->getOriginalFieldValue('content'));
    }

    public function testGetOriginalFieldValueNew()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $this->assertNull($article->getOriginalFieldValue('title'));
        $this->assertNull($article->getOriginalFieldValue('content'));

        // again, the same original
        $article->setTitle('bar');
        $this->assertNull($article->getOriginalFieldValue('title'));
    }

    public function testGetOriginalFieldValueNotNew()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'     => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'   => 'foo',
            'content' => 'bar',
        ));
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));

        $article->setTitle('ups');
        $article->setContent('spu');
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));

        // again, the same original
        $article->setTitle('some');
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
    }

    public function testGetOriginalFieldValueDefaultValues()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            'title'   => 'foo',
            'content' => 'bar',
        ));
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));
    }

    /*
     * getFieldsModified()
     */
    public function testGetFieldsModifiedNewNotModified()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertSame(array(), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNewModified()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');
        $article->setContent('bar');
        $this->assertSame(array(
            'title'   => null,
            'content' => null,
        ), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNotNew()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id'      => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title'    => 'bar',
            'content'  => 'foo',
            'isActive' => true,
        ));
        $this->assertSame(array(), $article->getFieldsModified());

        $article->setTitle('ups');
        $article->setIsActive(false);
        $this->assertSame(array(
            'title'    => 'bar',
            'isActive' => true,
        ), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNotNewDefaultValues()
    {
        $book = $this->mongator->create('Model\Book');
        $book->setDocumentData(array(
            '_id'   => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title' => 'foo',
        ));
        $this->assertSame(array(), $book->getFieldsModified());
    }

    /*
     * cleanFieldsModified
     */
    public function testCleanFieldsModified()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setId(new \MongoId(self::EXAMPLE_MONGO_ID));
        $article->setIsNew(false);
        $article->setTitle('foo');
        $article->setNote('bar');

        $article->clearFieldsModified();
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('bar'));
        $this->assertSame(array(), $article->getFieldsModified());
    }

    /*
     * isEmbeddedOneChanged
     */
    public function testIsEmbeddedOneChangedNewWithoutValue()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $source = $this->mongator->create('Model\Source');
        $this->assertFalse($source->isEmbeddedOneChanged('info'));
    }

    public function testIsEmbeddedOneChangedNewChange()
    {
        $source = $this->mongator->create('Model\Source')->setInfo($this->mongator->create('Model\Info'));
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $article = $this->mongator->create('Model\Article')->setSource($source);
        $this->assertTrue($article->isEmbeddedOneChanged('source'));
    }

    public function testIsEmbeddedOneChangedNotNew()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'foobar',
                ),
            ),
        ));
        $source = $article->getSource();
        $info = $source->getInfo();

        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertFalse($info->isEmbeddedOneChanged('info'));

        // change other
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertTrue($article->isEmbeddedOneChanged('source'));

        // change same
        $article->setSource($source);
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $source->setInfo($info);
        $this->assertFalse($info->isEmbeddedOneChanged('info'));
    }

    /*
     * getOriginalEmbeddedOneValue
     */
    public function testGetOriginalEmbeddedOneValueWithoutValue()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source = $this->mongator->create('Model\Article');
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));
    }

    public function testGetOriginalEmbeddedOneValueNew()
    {
        $article = $this->mongator->create('Model\Article')->setSource($this->mongator->create('Model\Source'));
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source = $this->mongator->create('Model\Source')->setInfo($this->mongator->create('Model\Info'));
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));

        // again, the same original
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));
    }

    public function testGetOriginalEmbeddedOneChangedNotNew()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'foobar',
                ),
            ),
        ));
        $source = $article->getSource();
        $info = $source->getInfo();

        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));

        // change
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));

        // change again, same original
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));

        // remove, same original
        $source->setInfo(null);
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource(null);
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));
    }

    /*
     * getEmbeddedsOneChanged()
     */
    public function testGetEmbeddedsOneChangedNewNotModified()
    {
        $article = $this->mongator->create('Model\Article');
        $this->assertSame(array(), $article->getEmbeddedsOneChanged());

        $source = $this->mongator->create('Model\Source');
        $this->assertSame(array(), $source->getEmbeddedsOneChanged());
    }

    public function testGetEmbeddedsOneChangedNewModified()
    {
        $article = $this->mongator->create('Model\Article')->setSource($this->mongator->create('Model\Source'));
        $this->assertSame(array('source' => null), $article->getEmbeddedsOneChanged());

        $source = $this->mongator->create('Model\Source')->setInfo($this->mongator->create('Model\Info'));
        $this->assertSame(array('info' => null), $source->getEmbeddedsOneChanged());
    }

    public function testGetEmbeddedsOneNotNew()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId(self::EXAMPLE_MONGO_ID),
            'source' => array('name' => 'foo'),
        ));
        $this->assertSame(array(), $article->getEmbeddedsOneChanged());

        $source = $article->getSource();
        $article->setSource($this->mongator->create('Model\Source'));
        $this->assertSame(array('source' => $source), $article->getEmbeddedsOneChanged());

        $source = $this->mongator->create('Model\Source')->setDocumentData(array(
            'info' => array('name' => 'bar'),
        ));
        $this->assertSame(array(), $source->getEmbeddedsOneChanged());

        $info = $source->getInfo();
        $source->setInfo($this->mongator->create('Model\Info'));
        $this->assertSame(array('info' => $info), $source->getEmbeddedsOneChanged());
    }

    /*
     * clearEmbeddedsOneChanged()
     */
    public function testClearEmbeddedsOneChanged()
    {
        $info = $this->mongator->create('Model\Info');
        $source = $this->mongator->create('Model\Source')->setInfo($info);
        $article = $this->mongator->create('Model\Article')->setSource($source);
        $article->clearEmbeddedsOneChanged();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));

        $info = $this->mongator->create('Model\Info');
        $source = $this->mongator->create('Model\Source')->setInfo($info);
        $source->clearEmbeddedsOneChanged();
        $this->assertFalse($source->isEmbeddedOneChanged('info'));
    }

    public function testReplaceReferenceGroupAfterRead()
    {
        $article = $this->mongator->create('Model\Article');

        $c1 = $this->mongator->create('Model\Category');
        $c2 = $this->mongator->create('Model\Category');
        $c3 = $this->mongator->create('Model\Category');

        $article->addCategories(array($c1, $c2));
        $article->save();

        $id = $article->getId();
        $repository = $this->mongator->getRepository('Model\Article');

        $article = $repository->findOneById($id);
        $article->getCategories()->replace(array($c3));
        $article->save();

        $article = $repository->findOneById($id);
        $this->assertEquals(1, count($article->getCategories()->all()));

        $c1->delete(); $c2->delete(); $c3->delete();
        $article->delete();
    }
}

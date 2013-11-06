![Mongator ODM](http://s8.postimg.org/pgkij7g6d/logo.png)

Mongator [![Build Status](https://secure.travis-ci.org/mongator/mongator.png)](http://travis-ci.org/mongator/mongator)
==============================

Mongator is to the ODMs what Mongo is to databases.

* **Simple**: Mongator is developed in a simple way. This makes it very easy to learn, use, and avoid bugs.
* **Powerful**: Mongator is very flexible thanks to Mondator, so you'll be able to use it to develop any type of application.
* **Ultrafast**: Mongator has been designed to be extremely light in memory consumption and processing cost.

¿Aren't you convinced yet? Let me show you a few more features:

* **References and Embeds**: Mongator allows you to work with [references and embeds] very easily.
* **Extensions**: Mongator can be customized infinitely with Mondator Extensions.
* **Indexes**: Mongator allows you to work easily with the [indexes] of the collections.
* **Events**: Mongator throws _hooks_ before and after inserting, updating, saving and deleting documents.
* **GridFS**: Mongator allows to save files of any size using [GridFS].
* **Log**: Mongator allows to save logs of the queries to improve the development.
* **batchInsert**: Mongator uses [batchInsert] to insert documents in an efficient way.
* **Atomic Operations**: Mongator uses [atomic operations] to update and delete documents efficiently.
* **Integratión with IDEs**: Mongator uses generated code, so you may integrate it with your IDE.
* **Tested**: Mongator is completely tested with automated test with [PHPUnit].

> Mongator is **the fastest mapper** in PHP by far.
> More information in the [performance comparison].


Requirements
------------

* PHP 5.3.x;
* ext-mongo > 1.2.11


Installation
------------

The recommended way of installing Mongator is [through composer](http://getcomposer.org).
You can see [package information on Packagist.](https://packagist.org/packages/mongator/mongator)

```JSON
{
    "require": {
        "mongator/mongator": "1.4.*"
    }
}
```


Examples
--------

```php
$query = $articleRepository->createQuery(); // Model\ArticleQuery
$query = $articleRepository->createQuery($criteria);

// methods (fluent interface)
$query
    ->criteria(array('is_active' => true))
    ->fields(array('title' => 1))
    ->sort(array('date' => -1))
    ->limit(10)
    ->skip(25)
    ->batchSize(3)
    ->hint(array('date' => 1))
    ->slaveOkay(true)
    ->snapshot(true)
    ->timeout(100)

    ->references() // Mongator's extra
;

// the real query is only executed in these cases
foreach ($query as $result) { // iterating (IteratorAggregate interface)
}
$articles = $query->all(); // retrieving all results explicitly
$article = $query->one(); // retrieving one result  explicitly

// counting results (directly, without hydrate)
$nb = $query->count();
$nb = count($query); // Countable interface
```

Tests
-----

Tests are in the `tests` folder.
To run them, you need PHPUnit.
Example:

    $ phpunit --configuration phpunit.xml.dist


License
-------

MIT, see [LICENSE](LICENSE)

<?php
$configClasses = require __DIR__.'/config_classes.php';

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->add('Mongator\Tests', __DIR__);
$loader->add('Mongator\Benchmarks', __DIR__);
$loader->add('Model', __DIR__);

// mondator
use Mandango\Mondator\Mondator;
use Mongator\Id\IdGeneratorContainer;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mongator\Extension\Core(array(
        'metadata_factory_class'  => 'Model\Mapping\Metadata',
        'metadata_factory_output' => __DIR__,
        'default_output'          => __DIR__,
    )),
    new Mongator\Extension\DocumentArrayAccess(),
    new Mongator\Extension\DocumentPropertyOverloading(),
));

IdGeneratorContainer::add('ab-id', 'Mongator\Tests\Id\ABIdGenerator');
$mondator->process();
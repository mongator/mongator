<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Type;

use Mongator\Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getTypeFunction($string)
    {
        eval('$function = function($from) { '.strtr($string, array(
            '%from%' => '$from',
            '%to%'   => '$to',
        )).' return $to; };');

        return $function;
    }
}

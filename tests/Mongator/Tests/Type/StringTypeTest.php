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

use Mongator\Type\StringType;

class StringTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new StringType();
        $this->assertSame('123', $type->toMongo(123));
    }

    public function testToPHP()
    {
        $type = new StringType();
        $this->assertSame('123', $type->toPHP(123));
    }

    public function testToMongoInString()
    {
        $type = new StringType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame('123', $function(123));
    }

    public function testToPHPInString()
    {
        $type = new StringType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame('123', $function(123));
    }
}

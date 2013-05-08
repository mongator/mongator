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

use Mongator\Type\RawType;

class RawTypeTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testToMongo($value)
    {
        $type = new RawType();
        $this->assertSame($value, $type->toMongo($value));
    }

    /**
     * @dataProvider provider
     */
    public function testToPHP($value)
    {
        $type = new RawType();
        $this->assertSame($value, $type->toPHP($value));
    }

    /**
     * @dataProvider provider
     */
    public function testToMongoInString($value)
    {
        $type = new RawType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame($value, $function($value));
    }

    /**
     * @dataProvider provider
     */
    public function testToPHPInString($value)
    {
        $type = new RawType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame($value, $function($value));
    }

    public function provider()
    {
        return array(
            array(array('foo' => 'bar')),
            array(new \DateTime()),
        );
    }
}

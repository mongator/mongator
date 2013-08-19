<?php

namespace Mongator\Tests\Id;
use Mongator\Id\BaseIdGenerator;

class ABIdGenerator extends BaseIdGenerator
{
    public function getCode(array $options)
    {
        return '%id% = new \Mongator\Tests\Id\ABId();';
    }

    public function getToMongoCode()
    {
        return <<<EOF
        if (!(%id% instanceof Mongator\Id\ABId)) {
            %id% = new Mongator\Tests\Id\ABId(%id%);
        }
EOF;
    }

    public function getToPHPCode()
    {
        return '%id% = new Mongator\Tests\Id\ABId(%id%);';
    }
}

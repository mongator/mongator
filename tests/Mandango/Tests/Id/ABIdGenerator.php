<?php

namespace Mandango\Tests\Id;
use Mandango\Id\BaseIdGenerator;

class ABIdGenerator extends BaseIdGenerator
{
    public function getCode(array $options) {
        return '%id% = new \Mandango\Tests\Id\ABId();';
    }

    public function getToMongoCode() {
        return <<<EOF
        if (!(%id% instanceof Mandango\Id\ABId)) {
            %id% = new Mandango\Tests\Id\ABId(%id%);
        }
EOF;
    }

    public function getToPHPCode() {
        return '%id% = new Mandango\Tests\Id\ABId(%id%);';
    }
}

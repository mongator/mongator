<?php

namespace Mandango\Tests\Id;

class ABId {
    public $a;
    public $b;

    public function __construct($id = null) {
        if ($id === null) {
            $this->a = new \MongoId();
            $this->b = new \MongoId();
        }
        else {
            $this->a = $id['a'];
            $this->b = $id['b'];
        }
    }

    public function __toString() {
        return sprintf('a:%s:b:%s', $this->a, $this->b);
    }
}

<?php
namespace Mongator\Query;

class ChunkResult extends \ArrayObject
{
    private $total;

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getData() { return $this->getArrayCopy(); }
    public function getTotal()
    {
        if ($this->total instanceOf \Closure) {
            $this->total = $this->total->__invoke();
        }

        return $this->total;
    }
}

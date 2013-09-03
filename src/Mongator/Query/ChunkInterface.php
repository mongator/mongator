<?php

namespace Mongator\Query;

interface ChunkInterface
{
    public function getResult(Query $query);
    public function set($sortStrategy, $page, $pageSize);
}

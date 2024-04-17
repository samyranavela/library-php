<?php

namespace App\Commons\Aggregates;

final  class AggregateRootIsStale extends \RuntimeException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}

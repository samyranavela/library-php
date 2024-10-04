<?php

namespace App\Commons\Aggregates;

use RuntimeException;

final class AggregateRootIsStale extends RuntimeException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}

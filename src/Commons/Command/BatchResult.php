<?php

namespace App\Commons\Command;

use App\Commons\Equatable;
use App\Commons\EquatableEnumTrait;

enum BatchResult: string implements Equatable
{
    use EquatableEnumTrait;

    case FullSuccess = 'FullSuccess';
    case SomeFailed = 'SomeFailed';
}

<?php

namespace App\Commons\Command;

use App\Commons\Equatable;
use App\Commons\EquatableEnumTrait;

enum Result: string implements Equatable
{
    use EquatableEnumTrait;

    case Success = 'Success';
    case Rejection = 'Rejection';
}

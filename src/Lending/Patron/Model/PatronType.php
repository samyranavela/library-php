<?php

namespace App\Lending\Patron\Model;

use App\Commons\Equatable;
use App\Commons\EquatableEnumTrait;

/**
 * @implements Equatable<PatronType>
 */
enum PatronType: string implements Equatable
{
    use EquatableEnumTrait;

    case Researcher = 'Researcher';
    case Regular = 'Regular';
}

<?php

namespace App\Catalogue;

use App\Commons\Equatable;
use App\Commons\EquatableEnumTrait;

/**
 * @implements Equatable<BookType>
 */
enum BookType: string implements Equatable
{
    use EquatableEnumTrait;

    case Restricted = 'Restricted';
    case Circulating = 'Circulating';
}

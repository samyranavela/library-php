<?php

namespace App\Lending\Book\Infrastructure;

enum BookState: string
{
    case Available = 'Available';
    case OnHold = 'OnHold';
    case CheckedOut = 'CheckedOut';
}

<?php

namespace App\Commons\Hateoas;

use Munus\Collection\GenericList;

class RepresentationModel
{
    private GenericList $links;

    public function __construct()
    {
        $this->links = GenericList::empty();
    }
}

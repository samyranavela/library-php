<?php

namespace App\Lending\PatronProfile\Web;

use Symfony\Component\Uid\Uuid;

final readonly class ProfileResource
{
    public function __construct(
        public Uuid $patronId,
        public Uuid $patronId,
    ) {
    }
}

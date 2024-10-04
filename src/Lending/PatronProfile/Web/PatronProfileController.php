<?php

namespace App\Lending\PatronProfile\Web;

use App\Lending\Patron\Application\Hold\CancelingHold;
use App\Lending\Patron\Application\Hold\PlacingOnHold;
use App\Lending\PatronProfile\Model\PatronProfiles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Uid\Uuid;

#[AsController]
final class PatronProfileController extends AbstractController
{
    public function __construct(
        private readonly PatronProfiles $patronProfiles,
        private readonly PlacingOnHold $placingOnHold,
        private readonly CancelingHold $cancelingHold,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter] Uuid $patronId
    ): JsonResponse {

        return $this->json('');
    }
}

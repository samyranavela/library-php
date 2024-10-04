<?php

namespace App\Lending\PatronProfile\Web;

use App\Commons\Hateoas\RepresentationModel;
use Psr\Link\EvolvableLinkInterface;
use Symfony\Component\Uid\Uuid;

function linKToRoute(string $string, array $options = []): EvolvableLinkInterface
{

}

function linKToController(string $controllerClass, string $method, array $options): EvolvableLinkInterface
{

}

final readonly class ProfileResource extends RepresentationModel
{
    public function __construct(
        public Uuid $patronId,
    ) {
        parent::__construct();
    }

    public static function create(Uuid $patronId): self
    {
        $instance = new self($patronId);
        $instance->add(linKToRoute('holds', ['patronId' => $patronId])->withRel('holds'));
        $instance->add(linKToController(PatronProfileController::class, 'findCheckouts', ['patronId' => $patronId])->withRel('checkouts'));
        $instance->add(linKToRoute('patrons')->withRel('self'));

        return $instance;
    }
}

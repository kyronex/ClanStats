<?php

namespace App\Dto\ClashRoyale\RiverRace;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class Participant
{
    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $name;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $fame;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $boatAttacks;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $decksUsed;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $tag;

    public function __construct(array $data)
    {
        $this->name = $data["name"] ?? "";
        $this->fame = $data["fame"] ?? 0;
        $this->boatAttacks = $data["boatAttacks"] ?? 0;
        $this->decksUsed = $data["decksUsed"] ?? 0;
        $this->tag = $data["tag"] ?? "";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFame(): int
    {
        return $this->fame;
    }

    public function getBoatAttacks(): int
    {
        return $this->boatAttacks;
    }

    public function getDecksUsed(): int
    {
        return $this->decksUsed;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}

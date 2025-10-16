<?php

namespace App\Dto\React;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class ClanSearchForm
{
    #[Assert\NotBlank(message: "Le nom du clan est requis")]
    #[Assert\Length(min: 2, max: 50)]
    #[Groups(["form"])]
    private string $nomClan = "";

    #[Assert\Type("integer")]
    #[Assert\Range(min: 2, max: 50)]
    #[Groups(["form"])]
    private $minMembers = null;

    #[Assert\Type("integer")]
    #[Assert\Range(min: 2, max: 50)]
    #[Groups(["form"])]
    private $maxMembers = null;

    #[Assert\Type("integer")]
    #[Assert\PositiveOrZero]
    #[Groups(["form"])]
    private $minScore = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->nomClan = $data["nomClan"] ?? "";
            $this->minMembers = $data["minMembers"] ?? null;
            $this->maxMembers = $data["maxMembers"] ?? null;
            $this->minScore = $data["minScore"] ?? null;
        }
    }

    public function getNomClan(): string
    {
        return $this->nomClan;
    }

    public function getMinMembers()
    {
        return $this->minMembers;
    }

    public function getMaxMembers()
    {
        return $this->maxMembers;
    }

    public function getMinScore()
    {
        return $this->minScore;
    }

    public function setNomClan(string $nomClan): void
    {
        $this->nomClan = $nomClan;
    }

    public function setMinMembers($minMembers): void
    {
        if ($minMembers === null || $minMembers === '' || $minMembers === 0) {
            $this->minMembers = null;
        } else {
            $this->minMembers = (int) $minMembers;
        }
    }

    public function setMaxMembers($maxMembers): void
    {
        if ($maxMembers === null || $maxMembers === '' || $maxMembers === 0) {
            $this->maxMembers = null;
        } else {
            $this->maxMembers = (int) $maxMembers;
        }
    }

    public function setMinScore($minScore): void
    {
        if ($minScore === null || $minScore === '' || $minScore === 0) {
            $this->minScore = null;
        } else {
            $this->minScore = (int) $minScore;
        }
    }
}

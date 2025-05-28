<?php

namespace App\Dto\ClashRoyale\Search;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class Clan
{
    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $name;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $clanScore;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $clanWarTrophies;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $donationsPerWeek;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $members;

    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $tag;

    public function __construct(array $data)
    {
        $this->name = $data["name"] ?? "";
        $this->clanScore = $data["clanScore"] ?? 0;
        $this->clanWarTrophies = $data["clanWarTrophies"] ?? 0;
        $this->donationsPerWeek = $data["donationsPerWeek"] ?? 0;
        $this->members = $data["members"] ?? 0;
        $this->tag = $data["tag"] ?? "";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClanScore(): int
    {
        return $this->clanScore;
    }

    public function getClanWarTrophies(): int
    {
        return $this->clanWarTrophies;
    }

    public function getDonationsPerWeek(): int
    {
        return $this->donationsPerWeek;
    }

    public function getMembers(): int
    {
        return $this->members;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}

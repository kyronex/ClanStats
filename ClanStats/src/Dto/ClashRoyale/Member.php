<?php

namespace App\Dto\ClashRoyale;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class Member
{
    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $tag;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $expLevel;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $trophies;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $role;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $clanRank;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $previousClanRank;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $donations;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $donationsReceived;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $lastSeen;

    public function __construct(array $data)
    {
        $this->name = $data["name"] ?? "";
        $this->tag = $data["tag"] ?? "";
        $this->expLevel = $data["expLevel"] ?? 0;
        $this->trophies = $data["trophies"] ?? 0;
        $this->role = $data["role"] ?? "";
        $this->clanRank = $data["clanRank"] ?? 0;
        $this->previousClanRank = $data["previousClanRank"] ?? 0;
        $this->donations = $data["donations"] ?? 0;
        $this->donationsReceived = $data["donationsReceived"] ?? 0;
        $this->lastSeen = $data["lastSeen"] ?? "";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getExpLevel(): int
    {
        return $this->expLevel;
    }

    public function getTrophies(): int
    {
        return $this->trophies;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getClanRank(): int
    {
        return $this->clanRank;
    }

    public function getPreviousClanRank(): int
    {
        return $this->previousClanRank;
    }

    public function getDonations(): int
    {
        return $this->donations;
    }

    public function getDonationsReceived(): int
    {
        return $this->donationsReceived;
    }

    public function getLastSeen(): string
    {
        return $this->lastSeen;
    }
}

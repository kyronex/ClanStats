<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


class WarStatsHistoriqueClanWar
{
    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $sessionId;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMaxFame;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMinFame;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMinBoatAttacks;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMaxBoatAttacks;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMinDecksUsed;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $reelMaxDecksUsed;

    #[Assert\Type("string")]
    #[Groups(["ajaxed"])]
    private array $players = [];

    public function __construct(array $data)
    {
        $this->sessionId = $data["sessionId"] ?? "";
        $this->reelMaxFame = $data["reelMaxFame"] ?? 1;
        $this->reelMinFame = $data["reelMinFame"] ?? 1;
        $this->reelMinBoatAttacks = $data["reelMinBoatAttacks"] ?? 1;
        $this->reelMaxBoatAttacks = $data["reelMaxBoatAttacks"] ?? 1;
        $this->reelMinDecksUsed = $data["reelMinDecksUsed"] ?? 1;
        $this->reelMaxDecksUsed = $data["reelMaxDecksUsed"] ?? 1;
        $this->players = $data["players"] ?? [];
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getReelMaxFame(): string
    {
        return $this->reelMaxFame;
    }

    public function getReelMinFame(): string
    {
        return $this->reelMinFame;
    }

    public function getReelMinBoatAttacks(): string
    {
        return $this->reelMinBoatAttacks;
    }

    public function getReelMaxBoatAttacks(): string
    {
        return $this->reelMaxBoatAttacks;
    }

    public function getReelMinDecksUsed(): string
    {
        return $this->reelMinDecksUsed;
    }

    public function getReelMaxDecksUsed(): string
    {
        return $this->reelMaxDecksUsed;
    }

    public function getTotalPlayers(): int
    {
        return count($this->players);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }
}

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

    #[Assert\Type("float")]
    #[Groups(["ajaxed"])]
    private float $medianFame;

    #[Assert\Type("float")]
    #[Groups(["ajaxed"])]
    private float $medianBoatAttacks;

    #[Assert\Type("float")]
    #[Groups(["ajaxed"])]
    private float $medianDecksUsed;

    #[Assert\Type("float")]
    #[Groups(["ajaxed"])]
    private float $medianContinuity;

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
        $this->medianFame = $data["medianFame"] ?? 0;
        $this->medianBoatAttacks = $data["medianBoatAttacks"] ?? 0;
        $this->medianDecksUsed = $data["medianDecksUsed"] ?? 0;
        $this->medianContinuity = $data["medianContinuity"] ?? 0;
        $this->players = $data["players"] ?? [];
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getReelMaxFame(): int
    {
        return $this->reelMaxFame;
    }

    public function getReelMinFame(): int
    {
        return $this->reelMinFame;
    }

    public function getReelMinBoatAttacks(): int
    {
        return $this->reelMinBoatAttacks;
    }

    public function getReelMaxBoatAttacks(): int
    {
        return $this->reelMaxBoatAttacks;
    }

    public function getReelMinDecksUsed(): int
    {
        return $this->reelMinDecksUsed;
    }

    public function getReelMaxDecksUsed(): int
    {
        return $this->reelMaxDecksUsed;
    }

    public function getMedianFame(): float
    {
        return $this->medianFame;
    }

    public function getMedianBoatAttacks(): float
    {
        return $this->medianBoatAttacks;
    }

    public function getMedianDecksUsed(): float
    {
        return $this->medianDecksUsed;
    }

    public function getMedianContinuity(): float
    {
        return $this->medianContinuity;
    }

    public function getTotalPlayers(): int
    {
        return count($this->players);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function toArray(): array
    {
        return [
            "sessionId" => $this->sessionId,
            "reelMaxFame" => $this->reelMaxFame,
            "reelMinFame" => $this->reelMinFame,
            "reelMinBoatAttacks" => $this->reelMinBoatAttacks,
            "reelMinDecksUsed" => $this->reelMinDecksUsed,
            "reelMaxBoatAttacks" => $this->reelMaxBoatAttacks,
            "reelMaxDecksUsed" => $this->reelMaxDecksUsed,
            "medianFame" => $this->medianFame,
            "medianBoatAttacks" => $this->medianBoatAttacks,
            "medianDecksUsed" => $this->medianDecksUsed,
            "medianContinuity" => $this->medianContinuity,
            "players" => $this->players,
        ];
    }
}

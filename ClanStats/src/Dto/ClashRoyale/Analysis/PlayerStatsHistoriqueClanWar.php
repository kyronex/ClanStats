<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\Analysis\War;

// TODO: ajout des calculs average
class PlayerStatsHistoriqueClanWar
{
    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $tag;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private bool $currentPlayer;

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(War::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $wars;

    public function __construct(array $data)
    {
        $this->tag = $data["tag"] ?? "";
        $this->name = $data["name"] ?? "";
        $this->currentPlayer = $data["currentPlayer"] ?? false;
        $this->createDtoWars($data["warList"]);
    }

    private function createDtoWars(array $wars): void
    {
        $this->wars = [];
        foreach ($wars as $key => $warData) {
            if ($warData instanceof War) {
                $this->wars[$key] = $warData;
            } elseif (is_array($warData)) {
                $this->wars[$key] = new War($warData);
            }
        }
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSeasonFame($sessionId): int
    {
        foreach ($this->wars as $war) {
            if ($war->getSessionId() === $sessionId) {
                return $war->getFame();
            }
        }
        return 0;
    }

    public function getTotalFame(): int
    {
        $totalFame = 0;
        foreach ($this->wars as $war) {
            $totalFame += $war->getFame();
        }
        return $totalFame;
    }

    public function getSeasonBoatAttacks($sessionId): int
    {
        foreach ($this->wars as $war) {
            if ($war->getSessionId() === $sessionId) {
                return $war->getBoatAttacks();
            }
        }
        return 0;
    }
    public function getTotalBoatAttacks(): int
    {
        $totalBoatAttacks = 0;
        foreach ($this->wars as $war) {
            $totalBoatAttacks += $war->getBoatAttacks();
        }
        return $totalBoatAttacks;
    }

    public function getSeasonDecksUsed($sessionId): int
    {
        foreach ($this->wars as $war) {
            if ($war->getSessionId() === $sessionId) {
                return $war->getDecksUsed();
            }
        }
        return 0;
    }

    public function getTotalDecksUsed(): int
    {
        $totalDecksUsed = 0;
        foreach ($this->wars as $war) {
            $totalDecksUsed += $war->getDecksUsed();
        }
        return $totalDecksUsed;
    }

    public function getWarsCount(): int
    {
        return count($this->wars);
    }


    public function getWars(): array
    {
        return $this->wars;
    }

    public function getCurrentPlayer(): bool
    {
        return $this->currentPlayer;
    }

    public function toArray(): array
    {
        return [
            "tag" => $this->tag,
            "name" => $this->name,
            "currentPlayer" => $this->currentPlayer,
            "wars" => $this->wars,
        ];
    }
}

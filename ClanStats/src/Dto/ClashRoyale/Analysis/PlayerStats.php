<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\Score;

class PlayerStats
{

    #[Assert\NotNull]
    #[Groups(["ajaxed"])]
    private readonly PlayerStatsHistoriqueClanWar $originalStats;

    #[Assert\Type('array')]
    #[Groups(["ajaxed"])]
    private array $fameRank = [];

    #[Assert\NotBlank]
    #[Groups(["ajaxed"])]
    private array $fameRankDown = [];

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private array $boatAttacksRank = [];

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private array $boatAttacksRankDown = [];

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private array $decksUsedRank = [];

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private array $decksUsedRankDown = [];

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Score::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $scores;

    public function __construct(array $data)
    {
        $this->originalStats = $data["originalStats"];
        $this->fameRank = $data["fameRank"];
        $this->fameRankDown = $data["fameRankDown"];
        $this->boatAttacksRank = $data["boatAttacksRank"];
        $this->boatAttacksRankDown = $data["boatAttacksRankDown"];
        $this->decksUsedRank = $data["boatAttacksRank"];
        $this->decksUsedRankDown = $data["boatAttacksRankDown"];
        $this->createDtoScores($data["scores"]);
    }

    private function createDtoScores(array $scores): void
    {
        $this->scores = [];
        foreach ($scores as $key => $scoresData) {
            if ($scoresData instanceof Score) {
                $this->scores[$key] = $scoresData;
            } elseif (is_array($scoresData)) {
                $this->scores[$key] = new Score($scoresData);
            }
        }
    }

    public function getOriginalStats(): PlayerStatsHistoriqueClanWar
    {
        return $this->originalStats;
    }

    public function getFameRank(): array
    {
        return $this->fameRank;
    }

    public function getFameRankDown(): array
    {
        return $this->fameRankDown;
    }

    public function getBoatAttacksRank(): array
    {
        return $this->boatAttacksRank;
    }

    public function getBoatAttacksRankDown(): array
    {
        return $this->boatAttacksRankDown;
    }

    public function getDecksUsedRank(): array
    {
        return $this->decksUsedRank;
    }

    public function getDecksUsedRankDown(): array
    {
        return $this->decksUsedRankDown;
    }

    public function getTag(): string
    {
        return $this->originalStats->getTag();
    }

    public function getName(): string
    {
        return $this->originalStats->getName();
    }

    public function getScores(): array
    {
        return $this->scores;
    }
}

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

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $fameRank = [];

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $fameRankDown = [];

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $boatAttacksRank = [];

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $boatAttacksRankDown = [];

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $decksUsedRank = [];

    #[Assert\Type("array")]
    #[Groups(["ajaxed"])]
    private array $decksUsedRankDown = [];

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Score::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $scoresInitial;

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Score::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $scoresNormalized;

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Score::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $scoresFinal;

    public function __construct(array $data)
    {
        $this->originalStats = $data["originalStats"];
        $this->fameRank = $data["fameRank"];
        $this->fameRankDown = $data["fameRankDown"];
        $this->boatAttacksRank = $data["boatAttacksRank"];
        $this->boatAttacksRankDown = $data["boatAttacksRankDown"];
        $this->decksUsedRank = $data["decksUsedRank"];
        $this->decksUsedRankDown = $data["decksUsedRankDown"];
        $this->createDtoScoresInitial($data["scoresInitial"]);
        $this->createDtoScoresNormalized($data["scoresNormalized"]);
        $this->createDtoScoresFinal($data["scoresFinal"]);
    }

    private function createScoresArray(array $scores): array
    {
        $result = [];
        foreach ($scores as $key => $scoresData) {
            if ($scoresData instanceof Score) {
                $result[$key] = $scoresData;
            } elseif (is_array($scoresData)) {
                $result[$key] = new Score($scoresData);
            }
        }
        return $result;
    }

    private function createDtoScoresInitial(array $scores): void
    {
        $this->scoresInitial = $this->createScoresArray($scores);
    }

    private function createDtoScoresFinal(array $scores): void
    {
        $this->scoresFinal = $this->createScoresArray($scores);
    }

    private function createDtoScoresNormalized(array $scores): void
    {
        $this->scoresNormalized = $this->createScoresArray($scores);
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

    public function getScoresInitial(): array
    {
        return $this->scoresInitial;
    }

    public function getScoresNormalized(): array
    {
        return $this->scoresNormalized;
    }

    public function getScoresFinal(): array
    {
        return $this->scoresFinal;
    }

    public function toArray(): array
    {
        return [
            "originalStats" => $this->originalStats->toArray(),
            "fameRank" => $this->fameRank,
            "fameRankDown" => $this->fameRankDown,
            "boatAttacksRank" => $this->boatAttacksRank,
            "boatAttacksRankDown" => $this->boatAttacksRankDown,
            "decksUsedRank" => $this->decksUsedRank,
            "decksUsedRankDown" => $this->decksUsedRankDown,
            "scoresInitial" => $this->scoresInitial,
            "scoresNormalized" => $this->scoresNormalized,
            "scoresFinal" => $this->scoresFinal,
        ];
    }
}

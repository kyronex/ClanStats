<?php

namespace App\Dto\ClashRoyale\RiverRace;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\RiverRace\Clan;

class RiverRaceLog
{
    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "riverRaceLogInfo"])]
    private int $seasonId;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "riverRaceLogInfo"])]
    private int $sectionIndex;

    #[Assert\NotBlank]
    #[Groups(["ajaxed", "riverRaceLogInfo"])]
    private string $createdDate;

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Clan::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $clans = [];

    public function __construct(array $data)
    {
        $this->seasonId = $data["seasonId"] ?? 0;
        $this->sectionIndex = $data["sectionIndex"] ?? 0;
        $this->createdDate = $data["createdDate"] ?? "";
        $this->createDtoClan($data["standings"]);
    }

    private function createDtoClan(array $dataStandings)
    {
        $this->clans = [];
        foreach ($dataStandings as $key => $value) {
            $this->clans[] = new Clan($value);
        }
    }

    public function getSeasonId(): int
    {
        return $this->seasonId;
    }

    public function getSectionIndex(): int
    {
        return $this->sectionIndex;
    }

    public function getCreatedDate(): string
    {
        return $this->createdDate;
    }

    public function getClans(): array
    {
        return $this->clans;
    }
}

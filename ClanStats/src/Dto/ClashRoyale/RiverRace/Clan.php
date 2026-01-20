<?php

namespace App\Dto\ClashRoyale\RiverRace;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\RiverRace\Participant;

class Clan
{
    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "clanInfoFlat"])]
    private int $rank;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "clanInfoFlat"])]
    private int $trophyChange;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private int $badgeId;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private int $clanScore;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private int $fame;

    #[Assert\NotBlank]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private string $finishTime;

    #[Assert\NotBlank]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(["ajaxed", "clanInfoDeep"])]
    private string $tag;

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Participant::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $participants;

    public function __construct(array $data)
    {
        $this->rank = $data["rank"] ?? 0;
        $this->trophyChange = $data["trophyChange"] ?? 0;
        $this->badgeId = $data["clan"]["badgeId"] ?? 0;
        $this->clanScore = $data["clan"]["clanScore"] ?? 0;
        $this->fame = $data["clan"]["fame"] ?? 0;
        $this->finishTime = $data["clan"]["finishTime"] ?? "";
        $this->name = $data["clan"]["name"] ?? "";
        $this->createDtoParticipant($data["clan"]);
        $this->tag = $data["clan"]["tag"] ?? "";
    }

    private function createDtoParticipant(array $dataClan)
    {
        $this->participants = [];
        foreach ($dataClan["participants"] as $value) {
            $this->participants[] = new Participant($value);
        }
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getTrophyChange(): int
    {
        return $this->trophyChange;
    }

    public function getBadgeId(): int
    {
        return $this->badgeId;
    }

    public function getClanScore(): int
    {
        return $this->clanScore;
    }

    public function getFame(): int
    {
        return $this->fame;
    }

    public function getFinishTime(): string
    {
        return $this->finishTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }
}

<?php

namespace App\Dto\ClashRoyale;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\Member;

class Clan
{
    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $tag;

    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $description;

    #[Assert\Type("integer")]
    #[Groups(["ajaxed"])]
    private int $badgeId;

    #[Assert\NotBlank]
    #[Groups(['ajaxed'])]
    private string $type;

    #[Assert\Type("integer")]
    #[Groups(['ajaxed'])]
    private int $requiredTrophies;

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

    #[Assert\NotNull]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type(Member::class)
    ])]
    #[Valid]
    #[Groups(["ajaxed"])]
    private array $membersList;

    public function __construct(array $data)
    {
        $this->name = $data["name"] ?? "";
        $this->tag = $data["tag"] ?? "";
        $this->description = $data["description"] ?? "";
        $this->badgeId = $data["badgeId"] ?? 0;
        $this->type = $data["type"] ?? "";
        $this->requiredTrophies = $data["requiredTrophies"] ?? 0;
        $this->clanScore = $data["clanScore"] ?? 0;
        $this->clanWarTrophies = $data["clanWarTrophies"] ?? 0;
        $this->donationsPerWeek = $data["donationsPerWeek"] ?? 0;
        $this->members = $data["members"] ?? 0;
        $this->createDtoMembers($data["memberList"]);
    }

    private function createDtoMembers(array $members): void
    {
        $this->membersList = [];
        foreach ($members as $memberData) {
            $this->membersList[] = new Member($memberData);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBadgeId(): int
    {
        return $this->badgeId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRequiredTrophies(): int
    {
        return $this->requiredTrophies;
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

    public function getMembersList(): array
    {
        return $this->membersList;
    }
}

<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Dto\ClashRoyale\Analysis\War;

/**
 * Agrégation des statistiques d'un joueur sur l'ensemble des guerres.
 */
class PlayerWarsSummary
{
  #[Assert\NotBlank]
  #[Groups(["ajaxed"])]
  private string $tag;

  #[Assert\NotBlank]
  #[Groups(["ajaxed"])]
  private string $name;

  #[Assert\Type("bool")]
  #[Groups(["ajaxed"])]
  private bool $currentPlayer;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $totalWarsParticipated;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $totalWarsFame;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $totalWarsBoatAttacks;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $totalWarsDecksUsed;

  #[Assert\Type("float")]
  #[Groups(["ajaxed"])]
  private float $averageWarsFame;

  #[Assert\Type("float")]
  #[Groups(["ajaxed"])]
  private float $averageWarsBoatAttacks;

  #[Assert\Type("float")]
  #[Groups(["ajaxed"])]
  private float $averageWarsDecksUsed;

  #[Assert\NotNull]
  #[Assert\Count(min: 0)]
  #[Assert\All([
    new Assert\Type(War::class)
  ])]
  #[Valid]
  #[Groups(["ajaxed"])]
  private array $wars;

  public function __construct(array $data)
  {
    $this->tag = $data['tag'];
    $this->name = $data['name'];
    $this->currentPlayer = $data['currentPlayer'] ?? false;
    $this->totalWarsParticipated = $data['totalWarsParticipated'] ?? 0;
    $this->totalWarsFame = $data['totalWarsFame'] ?? 0;
    $this->totalWarsBoatAttacks = $data['totalWarsBoatAttacks'] ?? 0;
    $this->totalWarsDecksUsed = $data['totalWarsDecksUsed'] ?? 0;
    $this->averageWarsFame = $data['averageWarsFame'] ?? 0;
    $this->averageWarsBoatAttacks = $data['averageWarsBoatAttacks'] ?? 0;
    $this->averageWarsDecksUsed = $data['averageWarsDecksUsed'] ?? 0;
    $this->createDtoWars($data["warList"] ?? []);
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

  public function getCurrentPlayer(): bool
  {
    return $this->currentPlayer;
  }

  public function getTotalWarsParticipated(): int
  {
    return $this->totalWarsParticipated;
  }

  public function getTotalWarsFame(): int
  {
    return $this->totalWarsFame;
  }

  public function getTotalWarsBoatAttacks(): int
  {
    return $this->totalWarsBoatAttacks;
  }

  public function getTotalWarsDecksUsed(): int
  {
    return $this->totalWarsDecksUsed;
  }

  public function getAverageWarsFame(): float
  {
    return $this->averageWarsFame;
  }

  public function getAverageWarsBoatAttacks(): float
  {
    return $this->averageWarsBoatAttacks;
  }

  public function getAverageWarsDecksUsed(): float
  {
    return $this->averageWarsDecksUsed;
  }

  public function getWarsCount(): int
  {
    return count($this->wars);
  }

  public function getWars(): array
  {
    return $this->wars;
  }

  public function toArray(): array
  {
    return [
      'tag' => $this->tag,
      'name' => $this->name,
      'currentPlayer' => $this->currentPlayer,
      'totalWarsParticipated' => $this->totalWarsParticipated,
      'totalWarsFame' => $this->totalWarsFame,
      'totalWarsBoatAttacks' => $this->totalWarsBoatAttacks,
      'totalWarsDecksUsed' => $this->totalWarsDecksUsed,
      'averageWarsFame' => $this->averageWarsFame,
      'averageWarsBoatAttacks' => $this->averageWarsBoatAttacks,
      'averageWarsDecksUsed' => $this->averageWarsDecksUsed,
      "wars" => $this->wars,
    ];
  }
}

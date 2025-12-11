<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


class War
{
  #[Assert\NotBlank]
  #[Groups(["ajaxed"])]
  private string $sessionId;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $fame;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $boatAttacks;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private int $decksUsed;
  public function __construct(array $data)
  {
    $this->sessionId = $data["sessionId"] ?? "";
    $this->fame = $data["fame"] ?? 0;
    $this->boatAttacks = $data["boatAttacks"] ?? 0;
    $this->decksUsed = $data["decksUsed"] ?? 0;
  }

  public function getSessionId(): string
  {
    return $this->sessionId;
  }

  public function getFame(): int
  {
    return $this->fame;
  }

  public function getBoatAttacks(): int
  {
    return $this->boatAttacks;
  }

  public function getDecksUsed(): int
  {
    return $this->decksUsed;
  }
}

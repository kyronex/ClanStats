<?php

namespace App\Dto\ClashRoyale\Analysis;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


// TODO: penser a faire une normalisation des scores
class Score
{
  #[Assert\NotBlank]
  #[Groups(["ajaxed"])]
  private string $sessionId;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $continuity; // continuity = lastContinuity + evoContinuity  ||| MAX = 1900 pour 100 et 20

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posFameRank; //  Pos - 1 / nbPlayers x 100 ||| on garde le % de position du score |||  Fame Max observer 3600

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $fameRank; // recupere le fame et applique la formule (multMax - (posFame(multMax - multMin) / 100 )) ||| Multiplicateur max = 1.25 ||| Multiplicateur min = 0.75 ||

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posFameRankDown;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $fameRankDown;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posBoatAttacksRank;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $boatAttacksRank;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posBoatAttacksRankDown;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $boatAttacksRankDown;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posDecksUsedRank;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $decksUsedRank;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $posDecksUsedRankDown;

  #[Assert\Type("integer")]
  #[Groups(["ajaxed"])]
  private float $decksUsedRankDown;

  public function __construct(array $data)
  {
    $this->sessionId = $data["sessionId"];

    $this->continuity = $data["continuity"];

    $this->posFameRank = $data["posFameRank"];
    $this->fameRank = $data["fameRank"];
    $this->posFameRankDown = $data["posFameRankDown"];
    $this->fameRankDown = $data["fameRankDown"];

    $this->posBoatAttacksRank = $data["posBoatAttacksRank"];
    $this->boatAttacksRank = $data["boatAttacksRank"];
    $this->posBoatAttacksRankDown = $data["posBoatAttacksRankDown"];
    $this->boatAttacksRankDown = $data["boatAttacksRankDown"];

    $this->posDecksUsedRank = $data["posDecksUsedRank"];
    $this->decksUsedRank = $data["decksUsedRank"];
    $this->posDecksUsedRankDown = $data["posDecksUsedRankDown"];
    $this->decksUsedRankDown = $data["decksUsedRankDown"];
  }

  public function getSessionId(): string
  {
    return $this->sessionId;
  }

  public function getContinuity(): float
  {
    return $this->continuity;
  }

  public function getPosFameRank(): float
  {
    return $this->posFameRank;
  }

  public function getFameRank(): float
  {
    return $this->fameRank;
  }

  public function getPosFameRankDown(): float
  {
    return $this->posFameRankDown;
  }

  public function getFameRankDown(): float
  {
    return $this->fameRankDown;
  }

  public function getPosBoatAttacksRank(): float
  {
    return $this->posBoatAttacksRank;
  }

  public function getBoatAttacksRank(): float
  {
    return $this->boatAttacksRank;
  }

  public function getPosBoatAttacksRankDown(): float
  {
    return $this->posBoatAttacksRankDown;
  }

  public function getBoatAttacksRankDown(): float
  {
    return $this->boatAttacksRankDown;
  }

  public function getPosDecksUsedRank(): float
  {
    return $this->posDecksUsedRank;
  }

  public function getDecksUsedRank(): float
  {
    return $this->decksUsedRank;
  }

  public function getPosDecksUsedRankDown(): float
  {
    return $this->posDecksUsedRankDown;
  }

  public function getDecksUsedRankDown(): float
  {
    return $this->decksUsedRankDown;
  }
}

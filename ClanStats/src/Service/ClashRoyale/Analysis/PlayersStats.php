<?php

namespace App\Service\ClashRoyale\Analysis;

use Psr\Log\LoggerInterface;
use App\Dto\ClashRoyale\Analysis\War;
use App\Dto\ClashRoyale\Analysis\WarStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\PlayerStats;
use App\Dto\ClashRoyale\Analysis\Score;

use App\Enum\PlayerMetric;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
// TODO mise en place d'un carry factor
class PlayersStats
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;
  private const TARGETS = [
    "fameRank",
    "fameRankDown",
    "boatAttacksRank",
    "boatAttacksRankDown",
    "decksUsedRank",
    "decksUsedRankDown"
  ];
  public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
  {
    $this->logger = $logger;
    $this->parameterBag = $parameterBag;
    $this->logger->info("Initialisation de : class 'PlayersStats'.");
  }

  /**
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats
   * @param array<string, PlayerStatsHistoriqueClanWar> $playersStats
   * @return array<string, mixed>
   */
  public function getPlayersAnalysisStats($warsStats, $playersStats)
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getPlayersAnalysisStats'.");
    $playersAnalysisStats = [];

    foreach ($playersStats as $playerKey => $playerStats) {
      $data = [];
      $data["originalStats"] = $playerStats;
      $data["fameRank"] = $this->getPosition(PlayerMetric::FAME, $warsStats, $playersStats, $playerKey, false);
      $data["fameRankDown"] = $this->getPosition(PlayerMetric::FAME, $warsStats, $playersStats, $playerKey, true);
      $data["boatAttacksRank"] = $this->getPosition(PlayerMetric::BOAT_ATTACKS, $warsStats, $playersStats, $playerKey, false);
      $data["boatAttacksRankDown"] = $this->getPosition(PlayerMetric::BOAT_ATTACKS, $warsStats, $playersStats, $playerKey, true);
      $data["decksUsedRank"] = $this->getPosition(PlayerMetric::DECKS_USED, $warsStats, $playersStats, $playerKey, false);
      $data["decksUsedRankDown"] = $this->getPosition(PlayerMetric::DECKS_USED, $warsStats, $playersStats, $playerKey, true);
      $data["scores"] = [];

      foreach ($warsStats as $warKey => $warStats) {
        if ($warKey !== "all") {
          if (!in_array($playerKey, $warStats->getPlayers())) continue;
          $score = [];
          $score = [...$score, "sessionId" => $warKey, "continuity" => 0];
          foreach (self::TARGETS as $target) {
            if (preg_match("/^fame/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::FAME, $warStats, $playerStats, $warKey, $target, $data)];
            } elseif (preg_match("/^boatAttacks/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::BOAT_ATTACKS, $warStats, $playerStats, $warKey, $target, $data)];
            } elseif (preg_match("/^decksUsed/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::DECKS_USED, $warStats, $playerStats, $warKey, $target, $data)];
            }
          }
          $data["scores"][$warKey] = $score;
          // $data["scores"][$warKey] = new Score($score);
        }
      }
      $data = $this->getScoreVariable($warsStats, $data);
      $playersAnalysisStats[$playerKey] = new PlayerStats($data);
    }
    return array_merge(["warsStats" => $warsStats], ["playersAnalysisStats" => $playersAnalysisStats]);
  }

  /**
   * @param WarStatsHistoriqueClanWar> $warsStats
   * @param array $dataPlayer
   * @return array<string, int>
   */
  public function getScoreVariable($warsStats, $dataPlayer)
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getScoreVariable'.");

    $wsNats =  array_keys($warsStats);
    natsort($wsNats);
    $wsNats = array_reverse(array_values($wsNats));
    $temporalMultiplier = [];
    $multiplier = (float)$this->parameterBag->get("clash_royale.score.init_temporality");
    foreach ($wsNats as $wsNat) {
      if ($wsNat !== "all") {
        $temporalMultiplier[$wsNat] = $multiplier;
        $multiplier -= $this->parameterBag->get("clash_royale.score.evo_temporality");
      }
    }
    $dataModified = $dataPlayer;

    foreach (array_reverse($dataPlayer["scores"]) as $tagWar => $value) {
      $previousWar = $this->getPreviousWar($temporalMultiplier, $tagWar);
      if ($previousWar !== "") {
        if (isset($dataModified["scores"][$previousWar]["continuity"])) {
          $oldContinuity = $dataModified["scores"][$previousWar]["continuity"];
          if ($oldContinuity < $this->parameterBag->get("clash_royale.score.init_continuity")) {
            $oldContinuity = $this->parameterBag->get("clash_royale.score.init_continuity");
          }
          $dataModified["scores"][$tagWar]["continuity"] = $oldContinuity + $this->parameterBag->get("clash_royale.score.evo_continuity");
        } else {
          // TODO Ajouter last continuity after $tagWar . Soustraire a decay_continuity compare avec init_continuity garde + grand
          $dataModified["scores"][$tagWar]["continuity"] = (float)$this->parameterBag->get("clash_royale.score.init_continuity");
        }
      } else {
        $dataModified["scores"][$tagWar]["continuity"] = (float)$this->parameterBag->get("clash_royale.score.init_continuity");
      }

      foreach (self::TARGETS as $target) {
        if (preg_match("/^fame/", $target, $matches)) {
          $dataModified["scores"][$tagWar][$target] = $dataModified["scores"][$tagWar][$target] * $temporalMultiplier[$tagWar];
        } elseif (preg_match("/^boatAttacks/", $target, $matches)) {
          $dataModified["scores"][$tagWar][$target] = $dataModified["scores"][$tagWar][$target] * $temporalMultiplier[$tagWar];
        } elseif (preg_match("/^decksUsed/", $target, $matches)) {
          $dataModified["scores"][$tagWar][$target] = $dataModified["scores"][$tagWar][$target] * $temporalMultiplier[$tagWar];
        }
      }
      $dataModified["scores"][$tagWar]["continuity"] = $dataModified["scores"][$tagWar]["continuity"] * $temporalMultiplier[$tagWar];
    }
    return $dataModified;
  }

  /**
   * @param array<string, int> $warsStats
   * @param string $tagWar
   * @return string
   */
  public function getPreviousWar($warsStats,  $tagWar)
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getPreviousWar'.");
    $previousWar = "";
    if (preg_match('/^(\d+)_(\d+)$/', $tagWar, $matches)) {
      $session = $matches[1];
      $section = $matches[2];
      if ($section > 0) {
        $previousWar = $session . "_" . ($section - 1);
      } else {
        $previousSession = $session - 1;
        $matchedKeys = array_filter(array_keys($warsStats), function ($key) use ($previousSession) {
          return  strpos($key, $previousSession) === 0;
        });
        $previousSection = array_reduce(array_values($matchedKeys), function ($carry, $wars) {
          if (preg_match('/^(\d+)_(\d+)$/', $wars, $matches)) {
            if ($matches[2] > $carry) {
              return $matches[2];
            } else {
              return $carry;
            }
          }
          return $carry;
        }, 0);
        $previousWar = $previousSession . "_" . $previousSection;
      }
      if (!isset($warsStats[$previousWar])) {
        $previousWar = "";
      }
    }
    return $previousWar;
  }

  /**
   * @param PlayerMetric $metric La métrique à analyser
   * @param WarStatsHistoriqueClanWar> $warsStats
   * @param  PlayerStatsHistoriqueClanWar $playersStats
   * @param string $tagPlayer
   * @param bool $warKey
   * @return array<string, int>
   */
  private function getScore(PlayerMetric $metric, $warStats, $playerStats, $warKey, $target, $data)
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getScore'.");
    $score = [];
    $score["pos" . ucfirst($target)] = ($data[$target][$warKey] - 1) / count($warStats->getPlayers()) * 100;
    $score[$target] = $metric->getValue($playerStats, $warKey) * $this->getPositionMultiplier($score["pos" . ucfirst($target)]);
    return $score;
  }
  public function getPositionMultiplier(float $position): float
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getPositionMultiplier'.");
    $range = $this->parameterBag->get("clash_royale.score.multiplier_max") - $this->parameterBag->get("clash_royale.score.multiplier_min");
    $positionRatio = $position / 100;
    return $this->parameterBag->get("clash_royale.score.multiplier_max") - ($positionRatio * $range);
  }

  /**
   * @param PlayerMetric $metric La métrique à analyser
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats
   * @param array<string, PlayerStatsHistoriqueClanWar> $playersStats
   * @param string $tagPlayer
   * @param bool $isDown
   * @return array<string, int>
   */
  private function getPosition(PlayerMetric $metric, $warsStats, $playersStats, $tagPlayer, $isDown = false)
  {
    $this->logger->info("Lancement de : class 'PlayersStats' function 'getPosition'.");
    $position = [];
    foreach ($warsStats as $warKey => $warStats) {
      if ($warKey !== "all") {
        if (!in_array($tagPlayer, $warStats->getPlayers())) continue;
        $currentValue = $metric->getValue($playersStats[$tagPlayer], $warKey);
        $betterPlayers = 0;
        foreach ($playersStats as $playerStats) {
          if ($playerStats->getTag() === $tagPlayer) continue;
          $otherValue = $metric->getValue($playerStats, $warKey);
          if ($otherValue === 0) continue;
          if ($isDown) {
            if ($otherValue >= $currentValue) {
              $betterPlayers++;
            }
          } else {
            if ($otherValue > $currentValue) {
              $betterPlayers++;
            }
          }
        }
        $position[$warKey] = $betterPlayers + 1;
      }
    }
    return $position;
  }
}

<?php

namespace App\Service\ClashRoyale\Analysis;

use Psr\Log\LoggerInterface;
use App\Dto\ClashRoyale\Analysis\WarStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\PlayerStats;
use App\Dto\ClashRoyale\Analysis\Score;

use App\Service\ClashRoyale\Analysis\AnalysisTools;

use App\Enum\PlayerMetric;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
// TODO mise en place d'un carry factor
class AnalysisPlayersStats
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;
  private AnalysisTools $analysisTools;

  private const TARGETS_RANK = [
    "fameRank",
    "fameRankDown",
    "boatAttacksRank",
    "boatAttacksRankDown",
    "decksUsedRank",
    "decksUsedRankDown"
  ];

  private const FIELDS_TO_ROUND = [
    "ceil" => [
      "fameRank",
      "boatAttacksRank",
      "decksUsedRank",
    ],
    "floor" => [
      "fameRankDown",
      "boatAttacksRankDown",
      "decksUsedRankDown",
      "continuity"
    ]
  ];
  public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger, AnalysisTools $analysisTools)
  {
    $this->logger = $logger;
    $this->parameterBag = $parameterBag;
    $this->analysisTools = $analysisTools;
    $this->logger->info("Initialisation de : class 'AnalysisPlayersStats'.");
  }

  /**
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats
   * @param array<string, PlayerStatsHistoriqueClanWar> $playersStats
   * @return array<string, mixed>
   */
  public function getPlayersAnalysisStats($warsStats, $playersStats)
  {
    $this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getPlayersAnalysisStats'.");
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
      $data["scoresInitial"] = [];

      foreach ($warsStats as $warKey => $warStats) {
        if ($warKey !== "all") {
          if (!in_array($playerKey, $warStats->getPlayers())) continue;
          $score = [];
          $score = [...$score, "sessionId" => $warKey, "continuity" => 0];
          foreach (self::TARGETS_RANK as $target) {
            if (preg_match("/^fame/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::FAME, $warStats, $playerStats, $warKey, $target, $data)];
            } elseif (preg_match("/^boatAttacks/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::BOAT_ATTACKS, $warStats, $playerStats, $warKey, $target, $data)];
            } elseif (preg_match("/^decksUsed/", $target, $matches)) {
              $score = [...$score, ...$this->getScore(PlayerMetric::DECKS_USED, $warStats, $playerStats, $warKey, $target, $data)];
            }
          }
          $data["scoresInitial"][$warKey] = new Score($score);
        }
      }
      $data = $this->getScoreNormalized($data);
      $data = $this->getScoreVariable($warsStats, $data);
      $playersAnalysisStats[$playerKey] = new PlayerStats($data);
    }
    $warsStats = $this->updateMedianContinuityWarStat($warsStats, $playersAnalysisStats);
    return array_merge(["warsStats" => $warsStats], ["playersAnalysisStats" => $playersAnalysisStats]);
  }

  /**
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats
   * @param array<string, PlayerStats> $playersStats
   * @return  int
   */
  public function updateMedianContinuityWarStat($warsStats, $playersStats)
  {
    $warsDto = [];
    foreach ($warsStats as $warKey => $warStats) {
      $newWarStats = $warStats->toArray();
      if ($warKey === "all") {
        $warsDto[$warKey] = new WarStatsHistoriqueClanWar($newWarStats);
        continue;
      }
      $scores = [];
      foreach ($playersStats as $playerKey => $playerStats) {
        if (!in_array($playerKey, $warStats->getPlayers())) continue;
        $scores[] = $playerStats->getSeasonScoresFinal($warKey)->getContinuity() ?? 0;;
      }

      $newWarStats["medianContinuity"] = $this->getFieldsToRound("continuity", $this->analysisTools->calculateMedian($scores));
      $warsDto[$warKey] = new WarStatsHistoriqueClanWar($newWarStats);
    }
    return $warsDto;
  }

  /**
   * @param array $dataPlayer
   * @return array
   */
  public function getScoreNormalized($dataPlayer)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getScoreNormalized'.");
    $dataModified = $dataPlayer;
    $dataModified["scoresNormalized"] = [];
    foreach ($dataModified["scoresInitial"] as $warKey => $score) {
      $scoreData = $score->toArray();
      foreach (self::TARGETS_RANK as $target) {
        $methodName = "get" . ucfirst($target);
        $originalValue = $score->$methodName();
        $newValue = 0;
        if (preg_match("/^decksUsed/", $target, $matches)) {
          $newValue = $originalValue * $this->parameterBag->get("clash_royale.score.normalisation_decks_used");
        } elseif (preg_match("/^boatAttacks/", $target, $matches)) {
          $newValue = $originalValue * $this->parameterBag->get("clash_royale.score.normalisation_boat_attacks");
        } else {
          $newValue = $originalValue;
        }
        $newValue = $this->getFieldsToRound($target, $newValue);
        $scoreData[$target] = $newValue;
      }
      $dataModified["scoresNormalized"][$warKey] = new Score($scoreData);
    }
    return $dataModified;
  }

  public function getFieldsToRound($target, $value)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getFieldsToRound'.");
    $newValue = 0;
    if (in_array($target, self::FIELDS_TO_ROUND["ceil"])) {
      $newValue = ceil($value);
    } elseif (in_array($target, self::FIELDS_TO_ROUND["floor"])) {
      $newValue = floor($value);
    }
    return $newValue;
  }

  /**
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats
   * @param array $dataPlayer
   * @return array<string, int>
   */
  public function getScoreVariable($warsStats, $dataPlayer)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getScoreVariable'.");
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
    $dataModified["scoresFinal"] = [];
    foreach (array_reverse($dataModified["scoresNormalized"]) as $warKey => $score) {
      $scoreData = $score->toArray();
      $previousWar = $this->getPreviousWar($temporalMultiplier, $warKey);
      $continuity = (float)$this->parameterBag->get("clash_royale.score.init_continuity");

      if ($previousWar !== ""  && isset($dataModified["scoresFinal"][$previousWar])) {
        $oldContinuity = $dataModified["scoresFinal"][$previousWar]->getContinuity();
        if ($oldContinuity < $this->parameterBag->get("clash_royale.score.init_continuity")) {
          $oldContinuity = $this->parameterBag->get("clash_royale.score.init_continuity");
        }
        $continuity = $oldContinuity + $this->parameterBag->get("clash_royale.score.evo_continuity");
      }
      $scoreData = [...$scoreData, "continuity" => $this->getFieldsToRound("continuity", $continuity * $temporalMultiplier[$warKey])];
      foreach (self::TARGETS_RANK as $target) {
        $methodName = "get" . ucfirst($target);
        $originalValue = $score->$methodName();
        $newValue =  $originalValue * $temporalMultiplier[$warKey];
        $scoreData[$target] = $this->getFieldsToRound($target, $newValue);
      }
      $dataModified["scoresFinal"][$warKey] = new Score($scoreData);
    }
    $dataModified["scoresFinal"] = array_reverse($dataModified["scoresFinal"]);
    return $dataModified;
  }

  /**
   * @param array<string, int> $warsStats
   * @param string $warKey
   * @return string
   */
  public function getPreviousWar($warsStats,  $warKey)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getPreviousWar'.");
    $previousWar = "";
    if (preg_match('/^(\d+)_(\d+)$/', $warKey, $matches)) {
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
   * @param PlayerMetric $metric
   * @param WarStatsHistoriqueClanWar $warStats
   * @param PlayerStatsHistoriqueClanWar $playerStats
   * @param string $warKey
   * @param string $target
   * @param array $data
   * @return array<string, float>
   */
  private function getScore(PlayerMetric $metric, $warStats, $playerStats, $warKey, $target, $data)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getScore'.");
    $score = [];
    $score["pos" . ucfirst($target)] = ($data[$target][$warKey] - 1) / count($warStats->getPlayers()) * 100;
    $score[$target] = $metric->getValue($playerStats, $warKey) * $this->getPositionMultiplier($score["pos" . ucfirst($target)]);
    return $score;
  }
  public function getPositionMultiplier(float $position): float
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getPositionMultiplier'.");
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
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getPosition'.");
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

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

/**
 * Service d'analyse des statistiques des joueurs pour les guerres de clan.
 *
 * Calcule les scores, positions et métriques de performance des joueurs
 * en tenant compte de la temporalité et de la continuité.
 */
class AnalysisPlayersStats
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;
  private AnalysisTools $analysisTools;

  /**
   * Cibles de classement à analyser.
   *
   * @var array<int, string>
   */
  private const TARGETS_RANK = [
    "fameRank",
    "fameRankDown",
    "boatAttacksRank",
    "boatAttacksRankDown",
    "decksUsedRank",
    "decksUsedRankDown"
  ];

  /**
   * Configuration de l'arrondi par type de métrique.
   *
   * @var array<string, array<int, string>>
   */
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

  /**
   * @param ParameterBagInterface $parameterBag Gestionnaire de paramètres
   * @param LoggerInterface $logger Logger Symfony
   * @param AnalysisTools $analysisTools Outils d'analyse statistique
   */
  public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger, AnalysisTools $analysisTools)
  {
    $this->logger = $logger;
    $this->parameterBag = $parameterBag;
    $this->analysisTools = $analysisTools;
    $this->logger->info("Initialisation de : class 'AnalysisPlayersStats'.");
  }

  /**
   * Génère les statistiques d'analyse complètes pour tous les joueurs.
   *
   * Calcule les positions, scores initiaux, normalisés et finaux pour chaque
   * joueur en tenant compte de la temporalité et de la continuité.
   *
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats Statistiques des guerres
   * @param array<string, PlayerStatsHistoriqueClanWar> $playersStats Statistiques des joueurs
   * @return array{warsStats: array<string, WarStatsHistoriqueClanWar>, playersAnalysisStats: array<string, PlayerStats>}
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
   * Met à jour les médianes de continuité et normalise les métriques pour chaque guerre.
   *
   * Calcule la médiane de continuité pour chaque guerre en se basant sur les
   * scores finaux des joueurs participants. Applique également les facteurs de
   * normalisation sur les médianes d'attaques de bateaux et de decks utilisés.
   *
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats Statistiques des guerres
   * @param array<string, PlayerStats> $playersStats Statistiques analysées des joueurs
   * @return array<string, WarStatsHistoriqueClanWar> Statistiques mises à jour
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

      $newWarStats["medianBoatAttacks"] = $this->getFieldsToRound("boatAttacksRank", $newWarStats["medianBoatAttacks"] * $this->parameterBag->get("clash_royale.score.normalisation_boat_attacks"));
      $newWarStats["medianDecksUsed"] = $this->getFieldsToRound("decksUsedRank", $newWarStats["medianDecksUsed"] * $this->parameterBag->get("clash_royale.score.normalisation_decks_used"));
      $newWarStats["medianContinuity"] = $this->getFieldsToRound("continuity", $this->analysisTools->calculateMedian($scores));
      $warsDto[$warKey] = new WarStatsHistoriqueClanWar($newWarStats);
    }
    return $warsDto;
  }

  /**
   * Normalise les scores initiaux en appliquant les coefficients de normalisation.
   *
   * Applique les facteurs de normalisation configurés pour les métriques
   * boatAttacks et decksUsed, puis arrondit selon la configuration.
   *
   * @param array{originalStats: PlayerStatsHistoriqueClanWar, scoresInitial: array<string, Score>} $dataPlayer Données du joueur
   * @return array{originalStats: PlayerStatsHistoriqueClanWar, scoresInitial: array<string, Score>, scoresNormalized: array<string, Score>} Données avec scores normalisés
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

  /**
   * Arrondit une valeur selon la stratégie définie pour la métrique cible.
   *
   * Applique ceil() pour les rangs montants et floor() pour les rangs
   * descendants et la continuité.
   *
   * @param string $target Nom de la métrique (ex: "fameRank", "continuity")
   * @param float $value Valeur à arrondir
   * @return int Valeur arrondie
   */
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
   * Calcule les scores finaux en appliquant les multiplicateurs temporels et de continuité.
   *
   * Applique un coefficient temporel décroissant aux guerres récentes et calcule
   * la continuité basée sur la participation aux guerres précédentes.
   *
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats Statistiques des guerres
   * @param array{scoresNormalized: array<string, Score>} $dataPlayer Données du joueur avec scores normalisés
   * @return array{scoresNormalized: array<string, Score>, scoresFinal: array<string, Score>} Données avec scores finaux
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
   * Trouve la clé de la guerre précédente dans la chronologie.
   *
   * Gère le passage d'une section à l'autre et d'une saison à l'autre.
   * Format attendu: "{saison}_{section}" (ex: "2024_3")
   *
   * @param array<string, float> $warsStats Statistiques des guerres avec multiplicateurs
   * @param string $warKey Clé de la guerre actuelle (format: "saison_section")
   * @return string Clé de la guerre précédente ou chaîne vide si non trouvée
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
   * Calcule le score pour une métrique spécifique en appliquant le multiplicateur de position.
   *
   * @param PlayerMetric $metric Type de métrique à calculer
   * @param WarStatsHistoriqueClanWar $warStats Statistiques de la guerre
   * @param PlayerStatsHistoriqueClanWar $playerStats Statistiques du joueur
   * @param string $warKey Identifiant de la guerre
   * @param string $target Nom de la cible de score (ex: "fameRank")
   * @param array{fameRank: array<string, int>, boatAttacksRank: array<string, int>, decksUsedRank: array<string, int>} $data Données de position précalculées
   * @return array{pos[Target]: float, [target]: float} Score calculé avec sa position normalisée
   */
  private function getScore(PlayerMetric $metric, $warStats, $playerStats, $warKey, $target, $data)
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getScore'.");
    $score = [];
    $score["pos" . ucfirst($target)] = ($data[$target][$warKey] - 1) / count($warStats->getPlayers()) * 100;
    $score[$target] = $metric->getValue($playerStats, $warKey) * $this->getPositionMultiplier($score["pos" . ucfirst($target)]);
    return $score;
  }

  /**
   * Calcule le multiplicateur de score basé sur la position du joueur.
   *
   * Applique une interpolation linéaire entre multiplier_max (pour les meilleurs)
   * et multiplier_min (pour les moins bons).
   *
   * @param float $position Position normalisée du joueur (0-100%)
   * @return float Multiplicateur de score (entre multiplier_min et multiplier_max)
   */
  public function getPositionMultiplier(float $position): float
  {
    //$this->logger->info("Lancement de : class 'AnalysisPlayersStats' function 'getPositionMultiplier'.");
    $range = $this->parameterBag->get("clash_royale.score.multiplier_max") - $this->parameterBag->get("clash_royale.score.multiplier_min");
    $positionRatio = $position / 100;
    return $this->parameterBag->get("clash_royale.score.multiplier_max") - ($positionRatio * $range);
  }

  /**
   * Calcule la position d'un joueur pour une métrique donnée dans une guerre.
   *
   * Compare la valeur du joueur avec celle de tous les autres participants
   * pour déterminer son classement.
   *
   * @param PlayerMetric $metric La métrique à analyser
   * @param array<string, WarStatsHistoriqueClanWar> $warsStats Statistiques des guerres
   * @param array<string, PlayerStatsHistoriqueClanWar> $playersStats Statistiques de tous les joueurs
   * @param string $tagPlayer Tag du joueur à analyser
   * @param bool $isDown Si true, égalité compte comme meilleur (pour rangs descendants)
   * @return array<string, int> Positions par guerre (warKey => position)
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

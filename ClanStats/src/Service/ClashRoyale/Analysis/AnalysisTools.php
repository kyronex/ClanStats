<?php

namespace App\Service\ClashRoyale\Analysis;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service d'outils statistiques pour l'analyse Clash Royale.
 *
 * Fournit des fonctions mathématiques et statistiques réutilisables
 * pour les calculs d'analyse des performances de clan.
 */
class AnalysisTools
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;

  /**
   * @param ParameterBagInterface $parameterBag Gestionnaire de paramètres de configuration
   * @param LoggerInterface $logger Logger Symfony pour traçabilité
   */
  public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
  {
    $this->logger = $logger;
    $this->parameterBag = $parameterBag;
    $this->logger->info("Initialisation de : class 'AnalysisTools'.");
  }


  /**
   * Calcule la moyenne d'un ensemble de valeurs numériques.
   *
   * @param array<int|float> $values Tableau de valeurs numériques (peut être non trié)
   * @return float La moyenne calculée
   */
  public function calculateAverage(array $values): float
  {
    //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'calculateAverage'.");
    $total = count($values);
    return array_sum($values) / $total;
  }

  /**
   * Calcule la médiane d'un ensemble de valeurs numériques.
   *
   * @param array<int|float> $values Tableau de valeurs numériques (peut être non trié)
   * @return float La médiane calculée
   */
  public function calculateMedian(array $values): float
  {
    //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'calculateMedian'.");
    sort($values, SORT_NUMERIC);
    $total = count($values);
    $milieu = floor($total / 2);
    if ($total % 2 === 0) {
      return ($values[$milieu - 1] + $values[$milieu]) / 2;
    } else {
      return $values[$milieu];
    }
  }
}

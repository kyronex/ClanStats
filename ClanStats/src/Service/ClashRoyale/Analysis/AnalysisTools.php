<?php

namespace App\Service\ClashRoyale\Analysis;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AnalysisTools
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;

  public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
  {
    $this->logger = $logger;
    $this->parameterBag = $parameterBag;
    $this->logger->info("Initialisation de : class 'AnalysisTools'.");
  }

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

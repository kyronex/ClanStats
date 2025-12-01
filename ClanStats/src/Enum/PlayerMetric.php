<?php

namespace App\Enum;

use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;

enum PlayerMetric: string
{
  // 🏆 MÉTRIQUES PRINCIPALES
  case FAME = 'fame';
  case BOAT_ATTACKS = 'boatAttacks';
  case DECKS_USED = 'DecksUsed';

  /**
   * @param PlayerStatsHistoriqueClanWar
   * @param string $warKey
   * @return int
   */
  public function getValue(PlayerStatsHistoriqueClanWar $player, string $warKey): int
  {
    return match ($this) {
      self::FAME => $player->getSeasonFame($warKey),
      self::BOAT_ATTACKS => $player->getSeasonBoatAttacks($warKey),
      self::DECKS_USED => $player->getSeasonDecksUsed($warKey),
    };
  }

  /**
   * @return string
   */
  public function getLabel(): string
  {
    return match ($this) {
      self::FAME => 'Fame',
      self::BOAT_ATTACKS => 'Attaques Bateau',
      self::DECKS_USED => 'Decks Utilisés',
    };
  }
}

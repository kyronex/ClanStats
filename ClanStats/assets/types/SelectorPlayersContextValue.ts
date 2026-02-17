import type { WarStatsHistoriqueClanWar, PlayerStats } from "../types";

export type SelectorPlayersContextValue = {
  // Données sources
  warsStats: { [key: string]: WarStatsHistoriqueClanWar };
  playersAnalysisStats: { [key: string]: PlayerStats };

  handleWarsSelect: (wars: Set<string> | null) => void;
  handlePlayersSelect: (players: { [key: string]: PlayerStats }) => void;

  // Config
  maxWars: number;
  maxPlayers: number | null;
  enableWarSelectAll: boolean;
  enablePlayerSelectAll: boolean;

  // Sélection guerres
  warsSelected: Set<string>;
  isWarsSelected: (key: string) => boolean;
  handleSelectedWar: (key: string) => void;
  clearWarsSelected: () => void;

  // Sélection joueurs
  playersSelected: Set<string>;
  isPlayersSelected: (tag: string) => boolean;
  handleSelectedPlayer: (tag: string) => void;
  clearPlayersSelected: () => void;

  // Données dérivées
  currentWars: string[];
  selectablePlayers: { [key: string]: PlayerStats };
  filteredPlayers: { [key: string]: PlayerStats };
  getValidSelectedPlayers: (playerTagsSet: Set<string> | null) => Set<string>;
  // Select all
  selectAllPlayers: boolean;
  selectAllWars: boolean;
  handleSelectAllPlayers: () => void;
  handleSelectAllWars: () => void;
};

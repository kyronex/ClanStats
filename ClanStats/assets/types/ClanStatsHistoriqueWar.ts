import { War } from "../types";
// TODO faire un mapping des types et fermer le type de WarStatsHistoriqueClanWar
export type Score = {
  sessionId: string;
  continuity: number;
  posFameRank: number;
  fameRank: number;
  posFameRankDown: number;
  fameRankDown: number;
  posBoatAttacksRank: number;
  boatAttacksRank: number;
  posBoatAttacksRankDown: number;
  boatAttacksRankDown: number;
  posDecksUsedRank: number;
  decksUsedRank: number;
  posDecksUsedRankDown: number;
  decksUsedRankDown: number;
  // TODO Cast plus pertinant
  [key: string]: string | number;
};

export type PlayerStatsHistoriqueClanWar = {
  tag: string;
  name: string;
  currentPlayer: boolean;
  warList: War;
};

export type PlayerStats = {
  originalStats: PlayerStatsHistoriqueClanWar;
  fameRank: number;
  fameRankDown: number;
  boatAttacksRank: number;
  boatAttacksRankDown: number;
  decksUsedRank: number;
  decksUsedRankDown: number;
  scoresInitial: { [sessionId: string]: Score };
  scoresNormalized: { [sessionId: string]: Score };
  scoresFinal: { [sessionId: string]: Score };
};

export type WarStatsHistoriqueClanWar = {
  sessionId: string;
  reelMaxFame: number;
  reelMinFame: number;
  reelMaxBoatAttacks: number;
  reelMinBoatAttacks: number;
  reelMaxDecksUsed: number;
  reelMinDecksUsed: number;
  medianFame: number;
  medianBoatAttacks: number;
  medianDecksUsed: number;
  medianContinuity: number;
  averageFame: number;
  averageBoatAttacks: number;
  averageDecksUsed: number;
  averageContinuity: number;
  players: string[];
  // TODO Cast plus pertinant
  [key: string]: string | number | string[];
};

export type ClanStatsHistoriqueWar = {
  warsStats: { [key: string]: WarStatsHistoriqueClanWar };
  playersAnalysisStats: { [key: string]: PlayerStats };
};

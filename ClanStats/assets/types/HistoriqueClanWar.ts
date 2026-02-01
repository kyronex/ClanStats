export type War = {
  warId: string;
  fame: number;
  boatAttacks: number;
  decksUsed: number;
};

export type HistoriqueClanWar = {
  name: string;
  tag: string;
  currentPlayer: boolean;
  totalWarsParticipated: number;
  totalWarsFame: number;
  totalWarsBoatAttacks: number;
  totalWarsDecksUsed: number;
  averageWarsFame: number;
  averageWarsBoatAttacks: number;
  averageWarsDecksUsed: number;
  wars?: War[];
};

export type Participant = {
  name: string;
  fame: number;
  boatAttacks: number;
  decksUsed: number;
  tag: string;
};

export type Clan = {
  rank: number;
  trophyChange: number;
  badgeId: number;
  clanScore: number;
  fame: number;
  finishTime: string;
  name: string;
  tag: string;
  participants: Participant[];
};

export type RiverRaceLog = {
  seasonId: string;
  sectionIndex: string;
  createdDate: string;
  clans: Clan[];
};

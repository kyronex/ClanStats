export type MemberList = {
  name: string;
  tag: string;
  expLevel: number;
  trophies: number;
  role: string;
  clanRank: number;
  previousClanRank: number;
  donations: number;
  donationsReceived: number;
  lastSeen: string;
};

export type ClanInfo = {
  name: string;
  tag: string;
  description?: string;
  badgeId?: number;
  type?: string;
  requiredTrophies?: number;
  clanScore: number;
  clanWarTrophies: number;
  donationsPerWeek: number;
  members: number;
  memberList?: MemberList[];
};

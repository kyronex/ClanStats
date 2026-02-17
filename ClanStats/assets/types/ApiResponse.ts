import { FetchErrors, ClanInfo, HistoriqueClanWar, RiverRaceLog, ClanStatsHistoriqueWar } from "../types";
export interface ApiResponse {
  success?: boolean;
  errors?: FetchErrors;
  [key: string]: unknown;
}

export interface ClanInfoApiResponse {
  success: boolean;
  message?: string;
  clan?: ClanInfo;
}

export interface HistoriqueClanWarApiResponse {
  success: boolean;
  message?: string;
  activeMembers?: HistoriqueClanWar[];
  exMembers?: HistoriqueClanWar[];
  taskId?: string;
}

export interface RiverRaceLogApiResponse {
  success: boolean;
  message?: string;
  riverRaceLogs?: RiverRaceLog[];
}

export interface StatsHistoriqueClanWarApiResponse {
  success: boolean;
  taskId?: string;
  status: string;
  data: ClanStatsHistoriqueWar;
}

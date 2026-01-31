import { FetchErrors, ClanInfo } from "../types";
export interface ApiResponse {
  success?: boolean;
  errors?: FetchErrors;
  [key: string]: unknown;
}

export interface ClanApiResponse {
  success: boolean;
  message?: string;
  clan?: ClanInfo;
}

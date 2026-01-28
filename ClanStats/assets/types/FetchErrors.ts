/* export type FetchErrors = {
  general?: string;
} & Record<string, string>; */

export interface FetchErrors {
  general?: string;
  [key: string]: string | undefined;
}

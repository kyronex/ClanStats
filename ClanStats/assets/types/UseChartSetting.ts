export type CategoryConfig = {
  label: string;
  active: boolean;
};

export type CategorySettings = {
  continuity: CategoryConfig;
  fameRank: CategoryConfig;
  boatAttacksRank: CategoryConfig;
  decksUsedRank: CategoryConfig;
  //[key: string]: CategoryConfig;
};

export type CategoryKey = keyof CategorySettings;
//export type DatasetsMap = { CategoryKey?: number[] };
export type DatasetsMap = Record<CategoryKey, number[]>;

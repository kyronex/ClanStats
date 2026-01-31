export type SortColumnConfig = {
  sort: boolean;
  active: boolean;
  order: number;
};

export type TabConfSort = Record<string, SortColumnConfig>;

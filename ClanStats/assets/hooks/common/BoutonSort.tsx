import React, { memo } from "react";
import type { TabConfSort } from "../../types";

type BoutonSortProps = {
  cle: string;
  handleEnabledSorts: (key: string) => void;
  handleWaySorts: (key: string) => void;
  tabConfSort: TabConfSort;
};

const BoutonSort = memo(function BoutonSort({ cle, handleEnabledSorts, handleWaySorts, tabConfSort }: BoutonSortProps) {
  const enabledSorts = () => handleEnabledSorts(cle);
  const waySorts = () => handleWaySorts(cle);
  return (
    <React.Fragment>
      <button onClick={enabledSorts}>
        {tabConfSort[cle].active ? "⚡" : "💤"}
        {tabConfSort[cle].order ? tabConfSort[cle].order : ""}
      </button>
      <button onClick={waySorts}>{tabConfSort[cle].sort ? "🔺" : "🔻"}</button>
    </React.Fragment>
  );
});

export { BoutonSort };

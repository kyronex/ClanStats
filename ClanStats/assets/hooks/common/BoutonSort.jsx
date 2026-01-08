import { memo } from "react";
import React from "react";

const BoutonSort = memo(function BoutonSort({ cle, handleEnabledSorts, handleWaySorts, tabConfSort }) {
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

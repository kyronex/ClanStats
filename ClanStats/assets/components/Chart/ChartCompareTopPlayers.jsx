import React from "react";
import { useChartCompareTopPlayers } from "../../hooks";

import { Radar } from "react-chartjs-2";

const ChartCompareTopPlayers = ({ warsStats, filteredData, warsSelected }) => {
  const { chartRefTop, optionsTop, formatedTopData, isEmpty } = useChartCompareTopPlayers(warsStats, filteredData, warsSelected);

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ width: "550px", height: "550px" }}>
      <Radar ref={chartRefTop} data={formatedTopData} options={optionsTop} />
    </div>
  );
};

export default ChartCompareTopPlayers;

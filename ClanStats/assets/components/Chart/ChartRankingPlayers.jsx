import React from "react";
import { useChartRankingPlayers } from "../../hooks";

import { Radar, Bar, Line } from "react-chartjs-2";
const ChartRankingPlayers = ({ warsStats, filteredData, warsSelected }) => {
  const { chartRefScore, chartRefTop, optionsScore, optionsTop, formatedScoreData, formatedTopData, isEmpty } = useChartRankingPlayers(
    warsStats,
    filteredData,
    warsSelected
  );

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ display: "flex", gap: "20px", padding: "20px" }}>
      <div style={{ width: "550px", height: "550px" }}>
        <Line ref={chartRefTop} data={formatedTopData} options={optionsTop} />
      </div>
    </div>
  );
};

export default ChartRankingPlayers;

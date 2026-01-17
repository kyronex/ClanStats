import React from "react";
import { useChartSingleRankingPlayers } from "../../hooks";

import { Bar } from "react-chartjs-2";
const ChartSingleRankingPlayers = ({ warsStats, filteredData, warsSelected }) => {
  const { isEmpty, chartRefSingleRank, optionsSingleRank, formatedSingleRankData } = useChartSingleRankingPlayers(
    warsStats,
    filteredData,
    warsSelected
  );

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ padding: "20px" }}>
      <div style={{ width: "70%", height: "650px" }}>
        <Bar ref={chartRefSingleRank} data={formatedSingleRankData} options={optionsSingleRank} />
      </div>
    </div>
  );
};

export default ChartSingleRankingPlayers;

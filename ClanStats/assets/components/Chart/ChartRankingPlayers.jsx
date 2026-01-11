import React from "react";
import { useChartRankingPlayers } from "../../hooks";
//TODO faire la configuration des graphiques
import { Line } from "react-chartjs-2";
const ChartRankingPlayers = ({ warsStats, filteredData, warsSelected }) => {
  const { isEmpty, chartRefRank, optionsRank, formatedRankData } = useChartRankingPlayers(warsStats, filteredData, warsSelected);

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ display: "flex", gap: "20px", padding: "20px" }}>
      <div style={{ width: "100%", height: "650px" }}>
        <Line ref={chartRefRank} data={formatedRankData} options={optionsRank} />
      </div>
    </div>
  );
};

export default ChartRankingPlayers;

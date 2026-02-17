//import React from "react";
import { useChartCompareScorePlayers } from "../../hooks";
import type { WarStatsHistoriqueClanWar, PlayerStats } from "../../types";
import { Bar } from "react-chartjs-2";

type ChartCompareScorePlayersProps = {
  warsStats: { [key: string]: WarStatsHistoriqueClanWar };
  filteredData: { [key: string]: PlayerStats };
  warsSelected: Set<string>;
};
const ChartCompareScorePlayers = ({ warsStats, filteredData, warsSelected }: ChartCompareScorePlayersProps) => {
  const { chartRefScore, optionsScore, formatedScoreData, isEmpty } = useChartCompareScorePlayers(warsStats, filteredData, warsSelected);

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ width: "500px", height: "500px" }}>
      <Bar ref={chartRefScore} data={formatedScoreData} options={optionsScore} />
    </div>
  );
};

export default ChartCompareScorePlayers;

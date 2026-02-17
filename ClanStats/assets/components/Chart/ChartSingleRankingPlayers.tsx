import { useChartSingleRankingPlayers } from "../../hooks";
import type { WarStatsHistoriqueClanWar, PlayerStats } from "../../types";
import { Bar } from "react-chartjs-2";

type ChartSingleRankingPlayersProps = {
  warsStats: { [key: string]: WarStatsHistoriqueClanWar };
  filteredData: { [key: string]: PlayerStats };
  warsSelected: Set<string>;
};

const ChartSingleRankingPlayers = ({ warsStats, filteredData, warsSelected }: ChartSingleRankingPlayersProps) => {
  const { isEmpty, chartRefSingleRank, optionsSingleRank, formatedSingleRankData } = useChartSingleRankingPlayers(
    warsStats,
    filteredData,
    warsSelected,
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

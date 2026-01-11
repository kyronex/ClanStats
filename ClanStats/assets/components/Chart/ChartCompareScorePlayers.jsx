import React from "react";
import { useChartCompareScorePlayers } from "../../hooks";

import { Bar } from "react-chartjs-2";

const ChartCompareScorePlayers = ({ warsStats, filteredData, warsSelected }) => {
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

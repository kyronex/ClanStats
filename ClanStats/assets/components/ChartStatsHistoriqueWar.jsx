import React from "react";
import { useChartCompare } from "../hooks";

import { Radar, Bar } from "react-chartjs-2";

const ChartStatsHistoriqueWar = ({ warsData = {}, filteredData = {}, playersSelected = {}, warsSelected = [] }) => {
  const { chartRefScore, chartRefTop, optionsScore, optionsTop, formatedScoreData, formatedTopData, isEmpty } = useChartCompare(
    warsData,
    filteredData,
    warsSelected
  );

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ display: "flex", gap: "20px", padding: "20px" }}>
      <div style={{ width: "500px", height: "500px" }}>
        <Bar ref={chartRefScore} data={formatedScoreData} options={optionsScore} />
      </div>

      <div style={{ width: "550px", height: "550px" }}>
        <Radar ref={chartRefTop} data={formatedTopData} options={optionsTop} />
      </div>
    </div>
  );
};

export default ChartStatsHistoriqueWar;

import React from "react";
import { useChartRankingPlayers } from "../../hooks";

import { Line } from "react-chartjs-2";
const ChartRankingPlayers = ({ warsStats, filteredData, warsSelected }) => {
  const { isEmpty, chartRefRank, optionsRank, formatedRankData, optionCategory, toggleCategory } = useChartRankingPlayers(
    warsStats,
    filteredData,
    warsSelected
  );

  if (isEmpty) {
    return <div style={{ padding: "20px", textAlign: "center" }}>Aucune donnée disponible</div>;
  }

  return (
    <div style={{ padding: "20px" }}>
      <div
        style={{
          display: "flex",
          justifyContent: "center",
          gap: "20px",
          marginBottom: "15px",
        }}
      >
        {Object.entries(optionCategory)?.map(([categoryKey, categoryData]) => {
          const handleChange = () => toggleCategory(categoryKey);
          return (
            <label
              key={categoryKey}
              style={{
                display: "flex",
                alignItems: "center",
                gap: "5px",
                cursor: "pointer",
              }}
            >
              <input type="checkbox" checked={categoryData.active} onChange={handleChange} />
              <span>{categoryData.label}</span>
            </label>
          );
        })}
      </div>

      <div style={{ width: "70%", height: "650px" }}>
        <Line ref={chartRefRank} data={formatedRankData} options={optionsRank} />
      </div>
    </div>
  );
};

export default ChartRankingPlayers;

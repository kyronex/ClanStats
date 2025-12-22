import { useClanStatsHistoriqueWar } from "../hooks";
import { useToggleSet } from "../hooks";
import { useChartFilter } from "../hooks";
import ChartStatsHistoriqueWar from "./ChartStatsHistoriqueWar.jsx";
import React, { useState, useEffect, useRef, useCallback, memo } from "react";

function ClanStatsHistoriqueWar({ taskId = "" }) {
  const { data: rData, status, isLoading, hasErrors } = useClanStatsHistoriqueWar(taskId);

  const { toggle: handleSelectedPlayer, has: isPlayersSelected, set: playersSelected } = useToggleSet([], { maxSize: 5 });
  const { toggle: handleSelectedWar, has: isWarsSelected, set: warsSelected } = useToggleSet([], { maxSize: 1 });
  const { filteredData } = useChartFilter(playersSelected, warsSelected, rData?.data.playersAnalysisStats, rData?.data.warsStats);

  useEffect(() => {
    if (rData?.data) {
      console.log("⭐ useEffect - Données disponibles");
      /*      console.log("📋 playersAnalysisStats:", Object.entries(rData.data.playersAnalysisStats));
      console.log("📋 warsStats:", Object.entries(rData.data.warsStats));
      console.log("📋 playersSelected:", playersSelected);
      console.log("📋 warsSelected:", warsSelected);
      console.log("📋 filteredData:", filteredData);
      console.log("📋 plop1:", Object.values(filteredData)[0].scoresFinal);
      */
    }
  }, [filteredData, playersSelected, warsSelected, rData]);

  if (isLoading) return <div>⚙️ Traitement...</div>;
  if (hasErrors) return <div>💥 Échec du traitement</div>;
  if (status === "pending") return <div>⏳ En attente...</div>;

  if (!rData?.data) return <div>📭 Aucune donnée disponible</div>;
  const playersAnalysisStats = Object.entries(rData.data.playersAnalysisStats);
  const warsStats = Object.entries(rData.data.warsStats);

  if (status === "completed") {
    return (
      <React.Fragment>
        <ChartStatsHistoriqueWar
          warsData={rData.data.warsStats}
          filteredData={filteredData}
          playersSelected={playersSelected}
          warsSelected={warsSelected}
        />
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
            gap: "10px",
            padding: "10px",
          }}
        >
          {warsStats
            .filter(([key]) => key.match(/^\d+_\d+$/))
            .map(([warKey, warData], index) => {
              return (
                <WarItem key={warKey} rowId={warKey} war={warData} isWarsSelected={isWarsSelected} handleSelectedWar={handleSelectedWar} />
              );
            })}
        </div>
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
            gap: "10px",
            padding: "10px",
          }}
        >
          {playersAnalysisStats.map(([playerTag, playerData], index) => {
            return (
              <PlayerItem
                key={playerTag}
                rowId={playerTag}
                player={playerData}
                isPlayersSelected={isPlayersSelected}
                handleSelectedPlayer={handleSelectedPlayer}
              />
            );
          })}
        </div>
      </React.Fragment>
    );
  }
  return <div></div>;
}

const WarItem = memo(function WarItem({ rowId, war, isWarsSelected, handleSelectedWar }) {
  const handleChange = () => handleSelectedWar(rowId);
  return (
    <div>
      <span>{war.sessionId}</span>
      <input type="checkbox" value={rowId} checked={isWarsSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

const PlayerItem = memo(function PlayerItem({ rowId, player, isPlayersSelected, handleSelectedPlayer }) {
  const handleChange = () => handleSelectedPlayer(rowId);
  return (
    <div>
      <span>{player.originalStats.name}</span>
      <input type="checkbox" value={rowId} checked={isPlayersSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export default ClanStatsHistoriqueWar;

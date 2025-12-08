import { useClanStatsHistoriqueWar } from "../hooks";
import ChartStatsHistoriqueWar from "./ChartStatsHistoriqueWar.jsx";

import React, { useState, useEffect, useRef, useCallback, memo } from "react";

function ClanStatsHistoriqueWar({ taskId = "" }) {
  const { data, status, isLoading, hasErrors } = useClanStatsHistoriqueWar(taskId);

  useEffect(() => {
    console.log("⭐ useEffect ClanStatsHistoriqueWar [taskId]");
    console.log(data);
  }, [data]);

  if (status === "error") return <div>💥 Échec du traitement</div>;
  if (status === "pending") return <div>⏳ En attente...</div>;
  if (status === "processing") return <div>⚙️ Traitement...</div>;

  if (status === "completed") {
    return (
      <div>
        {data.playersAnalysisStats?.map((player, index) => {
          return <PlayerItem key={`${player.originalStats.name}-${index}`} player={player} />;
        })}
        <ChartStatsHistoriqueWar />
      </div>
    );
  }
  return <div></div>;
}

const PlayerItem = memo(function PlayerItem({ player }) {
  return (
    <div>
      <span>{player.originalStats.tag}</span>
      <span>{player.originalStats.name}</span>
    </div>
  );
});

export default ClanStatsHistoriqueWar;

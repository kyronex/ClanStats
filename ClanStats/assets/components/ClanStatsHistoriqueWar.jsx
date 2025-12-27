import { useClanStatsHistoriqueWar } from "../hooks";
import { useToggleSet } from "../hooks";
import { useChartFilter } from "../hooks";

import ComparePlayers from "./Chart/ComparePlayers.jsx";
import RankingPlayers from "./Chart/RankingPlayers.jsx";

import React, { useState, useEffect, useRef, useCallback, memo } from "react";

function ClanStatsHistoriqueWar({ taskId }) {
  const { data: rData, status, isLoading, hasErrors } = useClanStatsHistoriqueWar(taskId);

  useEffect(() => {
    if (rData?.data) {
      console.log("⭐ useEffect - Données disponibles");
      /*      console.log("📋 playersAnalysisStats:", Object.entries(rData.data.playersAnalysisStats));
      console.log("📋 warsStats:", Object.entries(rData.data.warsStats));
      */
    }
  }, [rData]);

  if (isLoading) return <div>⚙️ Traitement...</div>;
  if (hasErrors) return <div>💥 Échec du traitement</div>;
  if (status === "pending") return <div>⏳ En attente...</div>;

  if (!rData?.data) return <div>📭 Aucune donnée disponible</div>;

  if (status === "completed") {
    return (
      <React.Fragment>
        <RankingPlayers rData={rData} />
        <ComparePlayers rData={rData} />
      </React.Fragment>
    );
  }
  return <div></div>;
}

export default ClanStatsHistoriqueWar;

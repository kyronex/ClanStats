import React, { useEffect } from "react";
import ComparePlayers from "./Chart/ComparePlayers.jsx";
import RankingPlayers from "./Chart/RankingPlayers.jsx";
import { useClanStatsHistoriqueWar } from "../hooks";

function ClanStatsHistoriqueWar({ taskId }) {
  const { data: rData, status, isLoading, hasErrors } = useClanStatsHistoriqueWar(taskId);

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

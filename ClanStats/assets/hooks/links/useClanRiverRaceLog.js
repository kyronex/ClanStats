import { useCallback } from "react";
import { useFetch } from "../../hooks";

const useClanRiverRaceLog = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors } = useFetch();
  const MESSAGES = {
    API_FAILURE: "Échec de la requête",
    NO_RESULT_DATA: "Aucune donnée dans la réponse",
    TECHNICAL_ERROR: "Erreur technique",
  };

  const clanRiverRaceLog = useCallback(
    async (clan) => {
      clearErrors();
      try {
        const result = await execute("/clanstats/riverRaceLog", {
          method: "POST",
          body: JSON.stringify({ tag: clan.tag }),
        });
        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          return { success: false, data: [], message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }
        if (!result.riverRaceLogs) {
          console.log("❌ Pas de propriété riverRaceLogs dans la réponse");
          return { success: false, data: [], message: MESSAGES.NO_RESULT_DATA };
        }

        const riverRaceLogs = result.riverRaceLogs.map((riverRaceLog) => ({
          createdDate: riverRaceLog.createdDate,
          seasonId: riverRaceLog.seasonId,
          sectionIndex: riverRaceLog.sectionIndex,
          clans: riverRaceLog.clans?.map((clan) => ({
            badgeId: clan.badgeId,
            clanScore: clan.clanScore,
            fame: clan.fame,
            finishTime: clan.finishTime,
            name: clan.name,
            rank: clan.rank,
            tag: clan.tag,
            trophyChange: clan.trophyChange,
            participants: clan.participants?.map((participant) => ({
              boatAttacks: participant.boatAttacks,
              decksUsed: participant.decksUsed,
              fame: participant.fame,
              name: participant.name,
              tag: participant.tag,
            })),
          })),
        }));
        return { success: true, data: riverRaceLogs, message: "" };
      } catch (error) {
        console.error("💥 Erreur lors de la recherche:", error);
        return { success: false, data: [], message: MESSAGES.API_FAILURE };
      }
    },
    [execute]
  );

  return {
    clanRiverRaceLog,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};

export { useClanRiverRaceLog };

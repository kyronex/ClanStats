import { useCallback } from "react";
import { useFetch } from "../../hooks";
import type { Result, RiverRaceLogApiResponse, ClanSearch, RiverRaceLog } from "../../types";

const useClanRiverRaceLog = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors, MESSAGES } = useFetch();

  const clanRiverRaceLog = useCallback(
    async (clan: ClanSearch): Promise<Result<RiverRaceLog[]>> => {
      clearErrors();
      try {
        const result = await execute<RiverRaceLogApiResponse>("/clanstats/riverRaceLog", {
          method: "POST",
          body: JSON.stringify({ tag: clan.tag }),
        });
        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          return { success: false, data: null, message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }
        if (!result.riverRaceLogs) {
          console.log("❌ Pas de propriété riverRaceLogs dans la réponse");
          return { success: false, data: null, message: MESSAGES.NO_RESULT_DATA };
        }

        const riverRaceLogs: RiverRaceLog[] = result.riverRaceLogs.map((riverRaceLog: RiverRaceLog) => ({
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
        return { success: false, data: null, message: MESSAGES.API_FAILURE };
      }
    },
    [execute],
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

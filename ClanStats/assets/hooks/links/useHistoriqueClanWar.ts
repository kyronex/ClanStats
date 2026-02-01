import { useCallback } from "react";
import { useFetch } from "../../hooks";
import { Result, HistoriqueClanWarApiResponse, HistoriqueClanWar, ClanSearch } from "../../types";

type HistoriqueClanWarData = {
  activeMembers: HistoriqueClanWar[];
  exMembers: HistoriqueClanWar[];
  taskId: string;
};

const useHistoriqueClanWar = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors, MESSAGES } = useFetch();

  const historiqueClanWar = useCallback(
    async (clan: ClanSearch, warsSelected: Set<string | number>): Promise<Result<HistoriqueClanWarData>> => {
      clearErrors();
      try {
        const formatMembers = (membersData: HistoriqueClanWar[]) =>
          Object.entries(membersData).map(([playerTag, playerData]) => ({
            tag: playerTag,
            name: playerData.name,
            currentPlayer: playerData.currentPlayer,
            averageWarsBoatAttacks: playerData.averageWarsBoatAttacks,
            averageWarsDecksUsed: playerData.averageWarsDecksUsed,
            averageWarsFame: playerData.averageWarsFame,
            totalWarsBoatAttacks: playerData.totalWarsBoatAttacks,
            totalWarsDecksUsed: playerData.totalWarsDecksUsed,
            totalWarsFame: playerData.totalWarsFame,
            totalWarsParticipated: playerData.totalWarsParticipated,
            wars: Object.entries(playerData.wars)
              .filter(([key]) => key.match(/^\d+_\d+$/))
              .map(([warKey, warData]) => ({
                warId: warKey,
                fame: warData.fame,
                boatAttacks: warData.boatAttacks,
                decksUsed: warData.decksUsed,
              })),
          }));

        const dataRequest = {
          clanTag: clan.tag,
          warsSelected: Array.from(warsSelected),
        };
        const result = await execute<HistoriqueClanWarApiResponse>("/clanstats/historiqueClanWar", {
          method: "POST",
          body: JSON.stringify(dataRequest),
        });
        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          return { success: false, data: null, message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }
        if (!result.exMembers || !result.activeMembers || !result.taskId) {
          console.log("❌ Pas de propriété exMembers ou activeMembers dans la réponse");
          return { success: false, data: null, message: MESSAGES.NO_RESULT_DATA };
        }

        return {
          success: true,
          data: { activeMembers: formatMembers(result.activeMembers), exMembers: formatMembers(result.exMembers), taskId: result.taskId },
          message: "",
        };
      } catch (error) {
        console.error("💥 Erreur lors de la requête:", error);
        return { success: false, data: null, message: MESSAGES.API_FAILURE };
      }
    },
    [execute],
  );

  return {
    historiqueClanWar,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};

export { useHistoriqueClanWar };

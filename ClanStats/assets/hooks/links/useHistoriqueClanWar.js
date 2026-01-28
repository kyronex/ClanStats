import { useCallback } from "react";
import { useFetch } from "../../hooks";

const useHistoriqueClanWar = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors, MESSAGES } = useFetch();

  const historiqueClanWar = useCallback(
    async (clan, warsSelected, activeMembers, exMembers, taskId) => {
      clearErrors();
      try {
        const formatMembers = (membersData) =>
          Object.entries(membersData).map(([playerTag, playerData]) => ({
            tag: playerTag,
            name: playerData.name,
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
        const result = await execute("/clanstats/historiqueClanWar", {
          method: "POST",
          body: JSON.stringify(dataRequest),
        });
        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          return { success: false, data: [], message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }
        if (!result.exMembers || !result.activeMembers) {
          console.log("❌ Pas de propriété exMembers ou activeMembers dans la réponse");
          return { success: false, data: [], message: MESSAGES.NO_RESULT_DATA };
        }
        if (!result.taskId) {
          console.log("❌ Pas de propriété taskId dans la réponse");
          return { success: false, data: [], message: MESSAGES.NO_RESULT_DATA };
        }

        exMembers(formatMembers(result.exMembers));
        activeMembers(formatMembers(result.activeMembers));
        taskId(result.taskId);
        return { success: true, data: [], message: "" };
      } catch (error) {
        console.error("💥 Erreur lors de la requête:", error);
        return { success: false, data: [], message: MESSAGES.API_FAILURE };
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

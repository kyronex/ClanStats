import { useCallback } from "react";
import { useFetch } from "../../hooks";

const useClanInfo = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors, MESSAGES } = useFetch();

  const clanInfo = useCallback(
    async (clan) => {
      clearErrors();
      try {
        const result = await execute("/clanstats/clan", {
          method: "POST",
          body: JSON.stringify({ tag: clan.tag }),
        });
        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          return { success: false, data: [], message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }
        if (!result.clan) {
          console.log("❌ Pas de propriété clans dans la réponse");
          return { success: false, data: [], message: MESSAGES.NO_RESULT_DATA };
        }

        const clanFind = {
          name: result.clan.name,
          tag: result.clan.tag,
          clanScore: result.clan.clanScore,
          clanWarTrophies: result.clan.clanWarTrophies,
          donationsPerWeek: result.clan.donationsPerWeek,
          members: result.clan.members,
          membersList: result.clan.membersList?.map((member) => ({
            name: member.name,
            tag: member.tag,
            expLevel: member.expLevel,
            trophies: member.trophies,
            role: member.role,
            clanRank: member.clanRank,
            previousClanRank: member.previousClanRank,
            donations: member.donations,
            donationsReceived: member.donationsReceived,
            lastSeen: member.lastSeen,
          })),
        };
        return { success: true, data: clanFind, message: "" };
      } catch (error) {
        console.error("💥 Erreur lors de la recherche:", error);
        return { success: false, data: [], message: MESSAGES.API_FAILURE };
      }
    },
    [execute],
  );

  return {
    clanInfo,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};

export { useClanInfo };

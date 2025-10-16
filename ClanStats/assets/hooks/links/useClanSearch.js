import { useCallback } from "react";
import useFetch from "../api/useFetch";
const useClanSearch = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors } = useFetch();
  const MESSAGES = {
    API_FAILURE: "Échec de la requête",
    NO_RESULT_DATA: "Aucune donnée dans la réponse",
    TECHNICAL_ERROR: "Erreur technique",
  };

  const searchClans = useCallback(
    async (searchData, onSearchResults) => {
      clearErrors();
      try {
        const result = await execute("/clanstats/search", {
          method: "POST",
          body: JSON.stringify(searchData),
        });

        if (!result?.success) {
          console.log("❌ Échec API - Status non success:", result?.message || MESSAGES.TECHNICAL_ERROR);
          onSearchResults?.([]);
          return { success: false, data: [], message: result?.message || MESSAGES.TECHNICAL_ERROR };
        }

        if (!result.clans) {
          console.log("❌ Pas de propriété clans dans la réponse");
          onSearchResults?.([]);
          return { success: false, data: [], message: MESSAGES.NO_RESULT_DATA };
        }

        if (!Array.isArray(result.clans) || result.clans.length === 0) {
          console.log("❌ Aucun clan trouvé pour cette recherche");
          onSearchResults?.([]);
          return { success: true, data: [], message: MESSAGES.NO_RESULTS };
        }

        const clans = result.clans.map((clan) => ({
          name: clan.name,
          tag: clan.tag,
          clanScore: clan.clanScore,
          clanWarTrophies: clan.clanWarTrophies,
          donationsPerWeek: clan.donationsPerWeek,
          members: clan.members,
        }));

        onSearchResults(clans);

        return { success: true, data: clans };
      } catch (error) {
        console.error("💥 Erreur lors de la recherche:", error);
        onSearchResults?.([]);
        return { success: false, data: [], message: MESSAGES.API_FAILURE };
      }
    },
    [execute]
  );

  return {
    searchClans,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};

export default useClanSearch;

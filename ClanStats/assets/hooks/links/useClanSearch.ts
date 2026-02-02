import { useCallback } from "react";
import { useFetch } from "../../hooks";
import type { Result, ClanSearch, SearchClanInput } from "../../types";

const useClanSearch = () => {
  const { execute, isLoading, errors, hasErrors, clearErrors, MESSAGES } = useFetch();

  const searchClans = useCallback(
    async (searchData: SearchClanInput): Promise<Result<ClanSearch[]>> => {
      clearErrors();
      try {
        const result = await execute("/clanstats/search", {
          method: "POST",
          body: JSON.stringify(searchData),
        });

        if (!result) {
          return { success: false, data: null };
        }

        if (!Array.isArray(result.clans) || result.clans.length === 0) {
          return { success: true, data: null, message: MESSAGES.NO_RESULT_DATA };
        }

        const clans: ClanSearch[] = result.clans.map((clan) => ({
          name: clan.name,
          tag: clan.tag,
          clanScore: clan.clanScore,
          clanWarTrophies: clan.clanWarTrophies,
          donationsPerWeek: clan.donationsPerWeek,
          members: clan.members,
        }));
        return { success: true, data: clans };
      } catch (error) {
        return { success: false, data: null, message: MESSAGES.API_FAILURE };
      }
    },
    [execute, clearErrors],
  );

  return {
    searchClans,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};
export { useClanSearch };

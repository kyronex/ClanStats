import { useState, useEffect, useRef } from "react";
import { useFetch } from "../../hooks";
import { StatsHistoriqueClanWarApiResponse } from "../../types";

const useClanStatsHistoriqueWar = (taskId: string) => {
  const [data, setData] = useState<StatsHistoriqueClanWarApiResponse | null>(null);
  const [status, setStatus] = useState<string>("idle");
  const { execute, isLoading, errors, hasErrors, clearErrors } = useFetch();
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (!taskId) return;
    const poll = async () => {
      clearErrors();
      try {
        const result = await execute<StatsHistoriqueClanWarApiResponse>("/clanstats/statsHistoriqueClanWar", {
          method: "POST",
          body: JSON.stringify({ taskId }),
        });

        if (!result?.success) {
          setStatus("error");
          return;
        }
        setStatus(result.status);

        // ✅ Logique de statut simplifiée
        if (result.status === "completed") {
          setData(result);
        } else if (["pending", "processing"].includes(result.status)) {
          timeoutRef.current = setTimeout(poll, 3000);
        } else if (result.status === "failed") {
          setStatus("error");
        }
      } catch (error) {
        console.error("💥 Erreur polling:", error);
        setStatus("error");
      }
    };

    poll();
    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [taskId]);

  return {
    data,
    status,
    isLoading,
    errors,
    hasErrors,
    clearErrors,
  };
};

export { useClanStatsHistoriqueWar };

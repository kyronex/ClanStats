import { useState, useEffect, useRef } from "react";
import useFetch from "../api/useFetch";

const useClanStatsHistoriqueWar = (taskId) => {
  const [data, setData] = useState(null);
  const [status, setStatus] = useState("idle");
  const { execute, isLoading, errors, hasErrors, clearErrors } = useFetch();
  const timeoutRef = useRef();

  useEffect(() => {
    if (!taskId) return;
    const poll = async () => {
      clearErrors();
      try {
        const result = await execute("/clanstats/statsHistoriqueClanWar", {
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

export default useClanStatsHistoriqueWar;

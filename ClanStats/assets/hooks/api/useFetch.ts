import { useState, useCallback } from "react";
import { FetchErrors } from "../../types";

interface ApiResponse {
  success?: boolean;
  errors?: FetchErrors;
  [key: string]: unknown;
}

const useFetch = () => {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [errors, setErrors] = useState<FetchErrors>({});

  const MESSAGES = {
    API_FAILURE: "Échec de la requête",
    NO_RESULT_DATA: "Aucune donnée dans la réponse",
    TECHNICAL_ERROR: "Erreur technique",
  };

  const execute = useCallback(async <T = ApiResponse>(url: string, options: RequestInit): Promise<T | null> => {
    setIsLoading(true);
    setErrors({});
    try {
      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-Internal-Request": "true",
        },
        ...options, // Permet d'override les options par défaut
      });

      if (!response.ok) {
        throw new Error(`Erreur ${response.status}: ${response.statusText}`);
      }

      const data = (await response.json()) as T;
      if ((data as ApiResponse)?.success) {
        return data;
      }
      setErrors(
        typeof (data as ApiResponse)?.errors === "object" && (data as ApiResponse).errors !== null
          ? (data as ApiResponse).errors
          : { general: "Une erreur est survenue" },
      );

      return null;
    } catch (error) {
      setErrors({
        general: error instanceof Error ? error.message : "Erreur inconnue",
      });
      return null;
    } finally {
      setIsLoading(false);
    }
  }, []);

  return {
    execute,
    isLoading,
    errors,
    hasErrors: Object.keys(errors).length > 0,
    clearErrors: () => setErrors({}),
    MESSAGES,
  };
};

export { useFetch };

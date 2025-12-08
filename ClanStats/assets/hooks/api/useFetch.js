import { useState, useCallback } from "react";

const useFetch = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const execute = useCallback(async (url, options) => {
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

      const data = await response.json();
      if (data.success) {
        return data;
      } else {
        setErrors(data.errors || { general: "Une erreur est survenue" });
        return null;
      }
    } catch (error) {
      console.error("💥 Erreur réseau:", error);
      setErrors({ general: error.message });
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
  };
};

export default useFetch;

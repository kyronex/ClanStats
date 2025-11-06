import { useCallback } from "react";
const useToggleSet = () => {
  const toggle = useCallback((prev, id) => {
    const newSet = new Set(prev);
    newSet.has(id) ? newSet.delete(id) : newSet.add(id);
    return newSet;
  }, []);

  return {
    toggle,
  };
};

export default useToggleSet;

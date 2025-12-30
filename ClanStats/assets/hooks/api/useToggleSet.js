import { useCallback, useState, useMemo } from "react";
const useToggleSet = (initialItems = [], options = {}) => {
  const [set, setSet] = useState(() => new Set(initialItems));
  const { maxSize } = options;
  const toggle = useCallback(
    (id) => {
      setSet((prev) => {
        if (maxSize && !prev.has(id) && prev.size >= maxSize) {
          return prev;
        }
        const newSet = new Set(prev);
        newSet.has(id) ? newSet.delete(id) : newSet.add(id);
        return newSet;
      });
    },
    [maxSize]
  );

  const has = useCallback((id) => set.has(id), [set]);

  const replace = useCallback((newSet) => {
    setSet(newSet instanceof Set ? newSet : new Set(newSet));
  }, []);

  const clear = useCallback(() => {
    setSet(new Set());
  }, []);

  const hash = useMemo(() => {
    return Array.from(set).sort().join(",");
  }, [set]);

  return {
    toggle,
    has,
    set,
    replace,
    clear,
    hash,
  };
};

export default useToggleSet;

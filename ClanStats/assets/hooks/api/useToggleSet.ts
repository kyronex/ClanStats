import { useCallback, useState, useMemo } from "react";

type UseToggleSetOptions = {
  maxSize?: number;
};

type UseToggleSetReturn<T> = {
  toggle: (id: T) => void;
  has: (id: T) => boolean;
  set: Set<T>;
  replace: (newSet: Set<T> | T[]) => void;
  clear: () => void;
  hash: string;
};

const useToggleSet = <T extends string | number>(initialItems: T[] = [], options: UseToggleSetOptions = {}): UseToggleSetReturn<T> => {
  const [set, setSet] = useState(() => new Set(initialItems));
  const { maxSize } = options;
  const toggle = useCallback(
    (id: T) => {
      setSet((prev) => {
        if (maxSize && !prev.has(id) && prev.size >= maxSize) {
          return prev;
        }
        const newSet = new Set(prev);
        newSet.has(id) ? newSet.delete(id) : newSet.add(id);
        return newSet;
      });
    },
    [maxSize],
  );

  const has = useCallback((id: T) => set.has(id), [set]);

  const replace = useCallback((newSet: Set<T> | T[]) => {
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
export { useToggleSet };

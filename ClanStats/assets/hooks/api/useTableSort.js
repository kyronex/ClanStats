import { useState, useCallback, useMemo } from "react";
const useTableSort = (initialKeys = [], initialData) => {
  const [tabConfSort, setConfTabSort] = useState(() => {
    const config = {};
    for (let key of Object.keys(initialKeys)) {
      config[key] = { sort: true, active: false, order: 0 };
    }
    return config;
  });

  const handleWaySorts = useCallback((key) => {
    setConfTabSort((prev) => {
      if (key in prev) {
        return {
          ...prev,
          [key]: { ...prev[key], sort: !prev[key].sort },
        };
      } else {
        return prev;
      }
    });
  }, []);

  const handleResetSorts = useCallback(() => {
    setConfTabSort((prev) => {
      const result = {};
      for (const key of Object.keys(prev)) {
        result[key] = { sort: true, active: false, order: 0 };
      }
      return result;
    });
  }, []);

  const handleEnabledSorts = useCallback((key) => {
    setConfTabSort((prev) => {
      if (key in prev) {
        if (!prev[key].active) {
          let orderMax = 0;
          Object.entries(prev).forEach(([key, value]) => {
            if (value.order > orderMax) orderMax = value.order;
          });
          return {
            ...prev,
            [key]: { ...prev[key], active: true, order: orderMax + 1 },
          };
        } else {
          const updatedSorts = {};
          Object.entries(prev).forEach(([nameKey, conf]) => {
            if (nameKey === key) {
              updatedSorts[key] = {
                ...conf,
                active: false,
                order: 0,
              };
            } else if (conf.order > prev[key].order) {
              updatedSorts[nameKey] = {
                ...conf,
                order: conf.order - 1,
              };
            } else {
              updatedSorts[nameKey] = conf;
            }
          });
          return updatedSorts;
        }
      } else {
        return prev;
      }
    });
  }, []);

  const handleShowTabConfSorts = useCallback(() => {
    console.log(`handleShowTabConfSorts`);
    console.log(tabConfSort);
  }, [tabConfSort]);

  const sortedData = useMemo(() => {
    const createMultiCriteriaComparator = (activeSorts) => {
      const confSorts = Object.entries(activeSorts).sort((a, b) => a[1].order - b[1].order);
      return (a, b) => {
        // Teste chaque critère séquentiellement
        for (const [key, config] of confSorts) {
          let comp = 0;
          if (typeof a[key] === "string" && typeof b[key] === "string") {
            if (config.sort === true) {
              comp = a[key].localeCompare(b[key]); // ✅ Tri alphabétique ascendant
            } else {
              comp = b[key].localeCompare(a[key]); // ✅ Tri alphabétique descendant
            }
          } else {
            if (config.sort === true) {
              comp = a[key] - b[key]; // Tri ascendant
            } else {
              comp = b[key] - a[key]; // Tri décroissant
            }
          }
          if (comp !== 0) return comp; // Premier critère discriminant
        }
        return 0; // Parfaitement égaux
      };
    };

    //const activeSorts = Object.entries(tabConfSort).filter(([key, config]) => config.active === true);
    const activeSorts = {};
    for (const key in tabConfSort) {
      if (tabConfSort[key].active === true) {
        activeSorts[key] = tabConfSort[key];
      }
    }

    if (Object.keys(activeSorts).length === 0) {
      return initialData;
    }

    /*
    const confSorts = Object.fromEntries(Object.entries(activeSorts).sort((a, b) => a[1].order - b[1].order));
      const currentSort1 = Object.entries(activeSorts).find((element, index, array) => {
       return element[1].order === step;
     });
     */
    const processedData = [...initialData].sort(createMultiCriteriaComparator(activeSorts));
    return processedData;
  }, [initialData, tabConfSort]);

  return {
    tabConfSort,
    sortedData,
    handleWaySorts,
    handleResetSorts,
    handleEnabledSorts,
    handleShowTabConfSorts,
  };
};

export { useTableSort };

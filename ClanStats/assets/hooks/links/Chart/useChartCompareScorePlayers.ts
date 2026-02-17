import { useMemo, useRef, useState } from "react";
import { useChartColorSettings } from "../../../hooks";
import { WarStatsHistoriqueClanWar, PlayerStats, CategorySettings, CategoryConfig, CategoryKey, DatasetsMap } from "../../../types";

import {
  Chart as ChartJS,
  RadialLinearScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
  CategoryScale,
  BarElement,
  Title,
  type ChartEvent,
  type LegendItem,
  type LegendElement,
  type TooltipItem,
} from "chart.js";

ChartJS.register(RadialLinearScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend, Title, BarElement, CategoryScale);
const useChartCompareScorePlayers = (
  warsStats: { [key: string]: WarStatsHistoriqueClanWar },
  filteredData: { [key: string]: PlayerStats },
  warsSelected: Set<string>,
) => {
  const { getColorSettingByIndex } = useChartColorSettings();

  const calculateOptimalMaxScoreChart = (playerValuesScore: Record<string, number>) => {
    const totalMax = Math.max(...Object.values(playerValuesScore));
    const maxWith110Percent = totalMax * 1.1;
    const roundedMax = Math.ceil(maxWith110Percent / 10) * 10;
    return roundedMax;
  };

  const defaultConfig = {
    continuity: { label: "Continuity", active: true },
    fameRank: { label: "Fame Rank", active: true },
    boatAttacksRank: { label: "Boat Attacks", active: true },
    decksUsedRank: { label: "Decks Used", active: true },
  };
  const [selectedCategory] = useState<CategorySettings>(defaultConfig);

  const currentWar = Array.from(warsSelected)[0];
  const chartRefScore = useRef<ChartJS<"bar"> | null>(null);

  const handleClickChartRefScore = (evt: ChartEvent, legendItem: LegendItem, legend: LegendElement<"bar">) => {
    ChartJS.defaults.plugins.legend.onClick.call(this, evt, legendItem, legend);
    setTimeout(() => {
      if (chartRefScore.current) {
        const chart = chartRefScore.current;
        const visibleMetas = chart.getSortedVisibleDatasetMetas();
        if (visibleMetas.length === 0) return;
        if (!chart.data.labels || chart.data.labels.length === 0) return;
        let playerValuesScore: Record<string, number> = {};
        chart.data.labels?.forEach((label, i) => {
          playerValuesScore[String(label)] = visibleMetas.reduce((sum, meta) => {
            const val = chart.data.datasets[meta.index].data[i];
            return sum + (typeof val === "number" ? val : Array.isArray(val) ? val[1] : 0);
          }, 0);
        });
        const newMax = calculateOptimalMaxScoreChart(playerValuesScore);
        chart.options.scales.y.max = newMax;
        //chart.update("none");
      }
    }, 50);
  };

  const isEmpty =
    Object.keys(filteredData).length === 0 ||
    !currentWar ||
    Object.keys(warsStats).length === 0 ||
    Object.values(filteredData).some((v) => v === undefined);

  const { formatedScoreData, dynamicMaxScore } = useMemo(() => {
    if (isEmpty) {
      return {
        formatedScoreData: { labels: [], datasets: [] },
        dynamicMaxScore: 100,
      };
    }

    const labels = [];
    const datasetsMap: DatasetsMap = {} as DatasetsMap;
    let playerValuesScore: Record<string, number> = {};

    (Object.keys(selectedCategory) as CategoryKey[]).forEach((key) => {
      datasetsMap[key] = [];
    });

    labels.push("Mediane");
    labels.push("Moyenne");
    playerValuesScore = { Mediane: 0, Moyenne: 0 };
    for (const [target, _] of Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]) {
      let newTarget = target.replace("Rank", "");
      const newTargetMedian = "median" + newTarget.charAt(0).toUpperCase() + newTarget.slice(1);
      const newTargetAverage = "average" + newTarget.charAt(0).toUpperCase() + newTarget.slice(1);
      const valueMedian = (warsStats[currentWar][newTargetMedian] as number) || 0;
      const valueAverage = (warsStats[currentWar][newTargetAverage] as number) || 0;
      datasetsMap[target].push(valueMedian);
      datasetsMap[target].push(valueAverage);
      playerValuesScore["Mediane"] += valueMedian;
      playerValuesScore["Moyenne"] += valueAverage;
    }

    for (const [playerTag, playerData] of Object.entries(filteredData)) {
      const warStats = playerData.scoresFinal?.[currentWar];
      if (!warStats) continue;
      if (!playerValuesScore[playerTag]) {
        playerValuesScore[playerTag] = 0;
      }
      labels.push(playerData.originalStats.name);
      for (const [target, _] of Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]) {
        const value = (warStats[target] as number) || 0;
        datasetsMap[target].push(value);
        playerValuesScore[playerTag] += value;
      }
    }

    const datasScore = (Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]).map(([key, conf], index) => {
      return {
        label: conf.label,
        data: datasetsMap[key],
        ...getColorSettingByIndex(index, Object.keys(filteredData).length, "bar"),
        stack: "Stack 0",
      };
    });

    const roundedMax = calculateOptimalMaxScoreChart(playerValuesScore);

    return {
      formatedScoreData: {
        labels: labels,
        datasets: datasScore,
      },
      dynamicMaxScore: roundedMax,
    };
  }, [filteredData, currentWar]);

  const optionsScore = useMemo(() => {
    const safeMaxScore = dynamicMaxScore || 100;
    return {
      responsive: true,
      maintainAspectRatio: false,
      aspectRatio: 1.2,

      plugins: {
        title: {
          display: true,
          text: "📊 Scores des Joueurs",
          font: { size: 16 },
          padding: { top: 5, bottom: 10 },
        },
        legend: {
          display: true,
          position: "top" as const,
          labels: {
            font: { size: 12 },
          },
          onClick: handleClickChartRefScore,
        },
        tooltip: {
          mode: "index" as const,
          intersect: false,
          callbacks: {
            footer: (items: TooltipItem<"bar">[]) => {
              const total = items.reduce((sum, item) => sum + item.parsed.y, 0);
              return `━━━━━━━━━━\nTotal: ${total}`;
            },
          },
        },
      },

      layout: {
        padding: {
          top: 10,
          right: 10,
          bottom: 10,
          left: 10,
        },
      },

      scales: {
        x: {
          stacked: true,
          grid: { display: false },
          ticks: {
            font: { size: 10 },
            maxRotation: 45,
            minRotation: 0,
          },
        },
        y: {
          stacked: true,
          beginAtZero: true,
          max: safeMaxScore,
          title: {
            display: true,
            text: "Score Total",
            font: { size: 12 },
          },
          ticks: {
            font: { size: 9 },
          },
        },
      },
    };
  }, [dynamicMaxScore]);

  return {
    isEmpty,
    chartRefScore,
    optionsScore,
    formatedScoreData,
  };
};

export { useChartCompareScorePlayers };

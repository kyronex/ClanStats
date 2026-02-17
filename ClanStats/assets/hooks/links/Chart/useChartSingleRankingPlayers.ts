import { useMemo, useRef, useCallback, useState } from "react";
import { useChartColorSettings } from "../../../hooks";
import type {
  WarStatsHistoriqueClanWar,
  PlayerStats,
  CategorySettings,
  CategoryConfig,
  CategoryKey,
  DatasetsMap,
  Score,
} from "../../../types";

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

const useChartSingleRankingPlayers = (
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

  const defaultConfig: CategorySettings = {
    continuity: { label: "Continuity", active: true },
    fameRank: { label: "Fame Rank", active: true },
    boatAttacksRank: { label: "Boat Attacks", active: true },
    decksUsedRank: { label: "Decks Used", active: true },
  };
  const [selectedCategory, setSelectedCategory] = useState<CategorySettings>(defaultConfig);

  const chartRefSingleRank = useRef<ChartJS<"bar"> | null>(null);
  const currentWar = Array.from(warsSelected)[0];

  const handleClickChartRefSingleRank = useCallback(
    (evt: ChartEvent, legendItem: LegendItem, legend: LegendElement<"bar">) => {
      const datasetLabel = legendItem.text;

      const metricKey = (Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]).find(
        ([_, config]) => config.label === datasetLabel,
      )?.[0];
      if (metricKey) {
        setSelectedCategory((prev) => ({
          ...prev,
          [metricKey]: {
            ...prev[metricKey],
            active: !prev[metricKey].active,
          },
        }));
      }
      ChartJS.defaults.plugins.legend.onClick.call(legend.chart, evt, legendItem, legend);
    },
    [selectedCategory],
  );

  const isEmpty =
    Object.keys(filteredData).length === 0 ||
    warsSelected.size === 0 ||
    Object.keys(warsStats).length === 0 ||
    Object.values(filteredData).some((v) => v === undefined);

  const { formatedSingleRankData, dynamicMaxScore } = useMemo(() => {
    if (isEmpty) {
      return {
        formatedSingleRankData: { labels: [], datasets: [] },
        dynamicMaxScore: 100,
      };
    }

    const labels: string[] = [];
    const datasetsMap: DatasetsMap = {} as DatasetsMap;
    let playerValuesScore: Record<string, number> = {};

    (Object.keys(selectedCategory) as CategoryKey[]).forEach((key) => {
      datasetsMap[key] = [];
    });

    labels.push("Mediane");
    labels.push("Moyenne");
    playerValuesScore = { Mediane: 0, Moyenne: 0 };
    for (const [target, conf] of Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]) {
      let newTarget = target.replace("Rank", "");
      const newTargetMedian = "median" + newTarget.charAt(0).toUpperCase() + newTarget.slice(1);
      const newTargetAverage = "average" + newTarget.charAt(0).toUpperCase() + newTarget.slice(1);

      const valueMedian = (warsStats[currentWar][newTargetMedian] as number) || 0;
      const valueAverage = (warsStats[currentWar][newTargetAverage] as number) || 0;
      datasetsMap[target].push(conf.active ? valueMedian : 0);
      datasetsMap[target].push(conf.active ? valueAverage : 0);
      if (conf.active) {
        playerValuesScore["Mediane"] += valueMedian;
        playerValuesScore["Moyenne"] += valueAverage;
      }
    }

    for (const [_, playerData] of Object.entries(filteredData)) {
      const warStats: Score = playerData.scoresFinal?.[currentWar];
      if (!warStats) continue;
      const playerName = playerData.originalStats.name;
      if (!playerValuesScore[playerName]) {
        playerValuesScore[playerName] = 0;
      }
      labels.push(playerName);
      for (const [target, conf] of Object.entries(selectedCategory) as [CategoryKey, CategoryConfig][]) {
        const value = warStats[target] || 0;
        datasetsMap[target].push(conf.active ? value : 0);
        if (conf.active) {
          playerValuesScore[playerName] += value;
        }
      }
    }

    const sortedIndices = labels
      .map((label, index) => ({ index, score: playerValuesScore[label] }))
      .sort((a, b) => b.score - a.score)
      .map((item) => item.index);

    const sortedLabels = sortedIndices.map((i) => labels[i]);

    const medianeIndex = sortedLabels.indexOf("Mediane");
    const averageIndex = sortedLabels.indexOf("Moyenne");

    const datasSingleRank = (Object.entries(defaultConfig) as [CategoryKey, CategoryConfig][]).map(([key, conf], index) => {
      const sortedData = sortedIndices.map((i) => datasetsMap[key][i]);
      const baseColors = getColorSettingByIndex(index, Object.keys(filteredData).length, "bar");

      return {
        label: conf.label,
        data: sortedData,
        hidden: !conf.active,
        ...baseColors,
        borderColor: sortedData.map((_, i) => (i === medianeIndex || i === averageIndex ? "#6e6e6e" : baseColors.borderColor)),
        stack: "Stack 0",
      };
    });

    const roundedMax = calculateOptimalMaxScoreChart(playerValuesScore);

    return {
      formatedSingleRankData: {
        labels: sortedLabels,
        datasets: datasSingleRank,
      },
      dynamicMaxScore: roundedMax,
    };
  }, [filteredData, warsSelected, selectedCategory]);

  const optionsSingleRank = useMemo(() => {
    const safeMaxScore = dynamicMaxScore || 100;
    return {
      indexAxis: "y" as const,
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
          onClick: handleClickChartRefSingleRank,
        },
        tooltip: {
          mode: "index" as const,
          intersect: true,
          callbacks: {
            footer: (items: TooltipItem<"bar">[]) => {
              const total = items.reduce((sum, item) => sum + item.parsed.x, 0);
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
        y: {
          stacked: true,
          grid: { display: false },
          ticks: {
            font: { size: 9 },
            autoSkip: false,
            maxTicksLimit: 100,
          },
        },
        x: {
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
    chartRefSingleRank,
    optionsSingleRank,
    formatedSingleRankData,
  };
};

export { useChartSingleRankingPlayers };

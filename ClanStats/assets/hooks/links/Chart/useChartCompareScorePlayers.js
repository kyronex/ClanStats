import { useMemo, useRef } from "react";
import { useChartColorSettings } from "../../../hooks";

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
} from "chart.js";

ChartJS.register(RadialLinearScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend, Title, BarElement, CategoryScale);
const useChartCompareScorePlayers = (warsStats, filteredData, warsSelected) => {
  const { getColorSettingByIndex } = useChartColorSettings();

  const calculateOptimalMaxScoreChart = (playerValuesScore) => {
    const totalMax = Math.max(...Object.values(playerValuesScore));
    const maxWith110Percent = totalMax * 1.1;
    const roundedMax = Math.ceil(maxWith110Percent / 10) * 10;
    return roundedMax;
  };

  const LABEL_SCORE = {
    continuity: "Continuity",
    fameRank: "Fame Rank",
    boatAttacksRank: "Boat Attacks",
    decksUsedRank: "Decks Used",
  };

  const currentWar = Array.from(warsSelected)[0];
  const chartRefScore = useRef(null);

  const handleClickChartRefScore = (evt, legendItem, legend) => {
    ChartJS.defaults.plugins.legend.onClick.call(this, evt, legendItem, legend);
    setTimeout(() => {
      if (chartRefScore.current) {
        const visibleMetas = chartRefScore.current.getSortedVisibleDatasetMetas();
        if (visibleMetas.length === 0) return;
        const allValues = visibleMetas.map((meta) => meta._dataset.data);
        const newMax = calculateOptimalMaxScoreChart(allValues);
        chartRefScore.current.options.scales.y.max = newMax;
        //chartRefScore.current.update("none");
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
    const datasetsMap = {};
    let playerValuesScore = {};

    Object.keys(LABEL_SCORE).forEach((key) => {
      datasetsMap[key] = [];
    });

    labels.push("Mediane");
    playerValuesScore = { Mediane: 0 };
    for (const [target, label] of Object.entries(LABEL_SCORE)) {
      let newTarget = target.replace("Rank", "");
      newTarget = "median" + newTarget.charAt(0).toUpperCase() + newTarget.slice(1);
      const value = warsStats[currentWar][newTarget] || 0;
      datasetsMap[target].push(value);
      playerValuesScore["Mediane"] += value;
    }

    for (const [playerTag, playerData] of Object.entries(filteredData)) {
      const warStats = playerData.scoresFinal?.[currentWar];
      if (!warStats) continue;
      if (!playerValuesScore[playerTag]) {
        playerValuesScore[playerTag] = 0;
      }
      labels.push(playerData.originalStats.name);
      for (const [target, label] of Object.entries(LABEL_SCORE)) {
        const value = warStats[target] || 0;
        datasetsMap[target].push(value);
        playerValuesScore[playerTag] += value;
      }
    }

    const datasScore = Object.entries(LABEL_SCORE).map(([key, label], index) => {
      return {
        label: label,
        data: datasetsMap[key],
        ...getColorSettingByIndex(index, Object.keys(filteredData).length, "bar"),
        stack: "Stack 0",
      };
    });

    const roundedMax = calculateOptimalMaxScoreChart(playerValuesScore); //Math.max(...Object.values(playerValuesScore));

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
          position: "top",
          labels: {
            font: { size: 12 },
          },
          onClick: handleClickChartRefScore,
        },
        tooltip: {
          mode: "index",
          intersect: false,
          callbacks: {
            footer: (items) => {
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

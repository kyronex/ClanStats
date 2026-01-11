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
const useChartCompareTopPlayers = (warsStats, filteredData, warsSelected) => {
  const { getColorSettingByIndex } = useChartColorSettings();

  const invertPercentage = (currentPercentage) => {
    return 100 - currentPercentage;
  };

  const currentWar = Array.from(warsSelected)[0];
  const chartRefTop = useRef(null);

  const isEmpty =
    Object.keys(filteredData).length === 0 ||
    !currentWar ||
    Object.keys(warsStats).length === 0 ||
    Object.values(filteredData).some((v) => v === undefined);

  const { formatedTopData } = useMemo(() => {
    if (isEmpty) {
      return {
        formatedScoreData: { labels: [], datasets: [] },
      };
    }

    const datasTop = Object.entries(filteredData)
      .map(([key, data], index) => {
        const warStats = data.scoresFinal?.[currentWar];
        if (!warStats) return null;

        return {
          label: data.originalStats.name,
          data: [
            invertPercentage(warStats.posFameRank) || 0,
            invertPercentage(warStats.posBoatAttacksRank) || 0,
            invertPercentage(warStats.posDecksUsedRank) || 0,
          ],
          ...getColorSettingByIndex(index, Object.keys(filteredData).length, "radar"),
        };
      })
      .filter(Boolean);

    return {
      formatedTopData: {
        labels: ["Top Fame Rank", "Top Boat Attacks", "Top Decks Used"],
        datasets: datasTop,
      },
    };
  }, [filteredData, currentWar]);

  const baseOptions = {
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 1.2,
    plugins: {
      legend: {
        display: true,
        position: "top",
        labels: {
          font: {
            size: 12,
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
  };

  const optionsTop = {
    ...baseOptions,
    aspectRatio: 1,
    plugins: {
      ...baseOptions.plugins,
      title: {
        display: true,
        text: "🏆 Tops des Joueurs",
        font: { size: 16 },
        padding: { top: 5, bottom: 10 },
      },
      legend: {
        ...baseOptions.plugins.legend,
        labels: {
          ...baseOptions.plugins.legend.labels,
          boxWidth: 12, // ✅ Réduit pour gagner de la place
        },
      },
      tooltip: {
        callbacks: {
          label: (context) => `${context.dataset.label}: ${context.parsed.r}`,
        },
      },
    },
    scales: {
      r: {
        beginAtZero: true,
        min: 0,
        max: 100,
        ticks: {
          stepSize: 20,
          font: { size: 9 }, // ✅ Réduit de 10 à 9
          backdropColor: "rgba(255, 255, 255, 0.75)",
        },
        pointLabels: {
          font: {
            size: 11,
            weight: "bold",
          },
        },
        grid: {
          color: "rgba(0, 0, 0, 0.1)",
          lineWidth: 1,
        },
        angleLines: {
          color: "rgba(0, 0, 0, 0.1)",
          lineWidth: 1,
        },
      },
    },
  };

  return {
    isEmpty,
    chartRefTop,
    optionsTop,
    formatedTopData,
  };
};

export { useChartCompareTopPlayers };

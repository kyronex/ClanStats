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
const useChartRankingPlayers = (warsStats, filteredData, warsSelected) => {
  const { getColorSettingByIndex } = useChartColorSettings();

  const LABEL_SCORE = {
    continuity: "Continuity",
    fameRank: "Fame Rank",
    boatAttacksRank: "Boat Attacks",
    decksUsedRank: "Decks Used",
  };

  const chartRefRank = useRef(null);

  const isEmpty =
    Object.keys(filteredData).length === 0 ||
    warsSelected.size === 0 ||
    Object.keys(warsStats).length === 0 ||
    Object.values(filteredData).some((v) => v === undefined);

  const { formatedRankData, dynamicMaxScore } = useMemo(() => {
    if (isEmpty) {
      return {
        formatedRankData: { labels: [], datasets: [] },
        dynamicMaxScore: 100,
      };
    }

    const labels = Array.from(warsSelected).sort();
    let maxScore = 0;

    const shuffleArray = (array) => {
      const shuffled = [...array];
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    };

    const datasRank = shuffleArray(Object.entries(filteredData))
      .map(([key, data], index) => {
        // if (!labels.every((label) => data.scoresFinal?.[label])) return null;

        let playerValuesScore = [];
        let playerValuesDetails = [];
        for (const warKey of labels) {
          let score = null;
          let details = [];
          if (data.scoresFinal?.[warKey]) {
            for (const [target, label] of Object.entries(LABEL_SCORE)) {
              const value = data.scoresFinal?.[warKey][target] || 0;
              score += value;
              details[target] = value;
            }
          }
          if (maxScore < score) maxScore = score;
          playerValuesScore.push(score);
          playerValuesDetails.push(details);
        }

        return {
          label: data.originalStats.name,
          data: playerValuesScore,
          ...getColorSettingByIndex(index, Object.keys(filteredData).length, "line"),
        };
      })
      .filter(Boolean);

    const maxWith110Percent = maxScore * 1.1;
    const roundedMax = Math.ceil(maxWith110Percent / 10) * 10;
    return {
      formatedRankData: {
        labels: labels,
        datasets: datasRank,
      },
      dynamicMaxScore: roundedMax,
    };
  }, [filteredData, warsSelected]);

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
        left: 10,
      },
    },
  };

  const optionsRank = useMemo(() => {
    const safeMaxScore = dynamicMaxScore || 100;
    return {
      responsive: true,
      maintainAspectRatio: false,
      aspectRatio: 1.5,

      plugins: {
        title: {
          display: true,
          text: "🏆 Évolution des Scores",
          font: { size: 16, weight: "bold" },
        },
        legend: {
          display: true,
          position: "bottom",
          labels: {
            boxWidth: 12,
            padding: 8,
            font: { size: 10 },
            usePointStyle: true,
            pointStyle: "circle",
            sort: (a, b) => a.text.localeCompare(b.text),
          },
          // ✅ Scrollable si trop de joueurs
          maxHeight: 400,
        },
        tooltip: {
          mode: "index",
          intersect: false,
          itemSort: (a, b) => b.raw - a.raw,
        },
      },

      scales: {
        x: {
          display: true,
          grid: {
            display: false,
          },
          ticks: {
            maxRotation: 45, // ✅ Rotation des labels si besoin
            minRotation: 0,
            callback: function (value, index) {
              const label = this.getLabelForValue(value);
              const datasets = this.chart.data.datasets;
              let count = 0;
              datasets.forEach((dataset) => {
                if (dataset.data[index] !== null && dataset.data[index] !== undefined) {
                  count++;
                }
              });
              return `${label} (${count})`;
            },
          },
        },
        y: {
          beginAtZero: false,
          max: safeMaxScore,
          grid: {
            color: "rgba(0, 0, 0, 0.1)",
          },
          stepSize: Math.ceil(safeMaxScore / 10 / 500) * 500,
        },
      },
      elements: {
        point: {
          radius: 4, // ✅ Points plus petits
          hoverRadius: 7,
          borderWidth: 1,
        },
        line: {
          tension: 0.2,
          borderWidth: 1, // ✅ Lignes plus fines
          spanGaps: false, // Ne pas connecter les points autour du trou
        },
      },
      interaction: {
        mode: "nearest",
        axis: "x",
        intersect: false,
      },
    };
  }, [dynamicMaxScore]);

  return {
    isEmpty,
    chartRefRank,
    optionsRank,
    formatedRankData,
  };
};

export { useChartRankingPlayers };

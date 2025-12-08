import React from "react";

import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from "chart.js";
//import { Chart as ChartJS, RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from "chart.js";
import { Line } from "react-chartjs-2";
//import { Radar } from "react-chartjs-2";

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);
//ChartJS.register(RadialLinearScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const ChartStatsHistoriqueWar = () => {
  // 📊 Données du graphique
  const data = {
    labels: ["Janvier", "Février", "Mars", "Avril", "Mai"],
    datasets: [
      {
        label: "🏆 Trophées",
        data: [2400, 2650, 2500, 2800, 3100],
        borderColor: "#FFD700",
        backgroundColor: "rgba(255, 215, 0, 0.1)",
        borderWidth: 2,
        tension: 0.4, // Courbe lissée
      },
    ],
  };

  // ⚙️ Options du graphique
  const options = {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: "📈 Évolution des Trophées",
      },
      legend: {
        position: "top",
      },
    },
    scales: {
      y: {
        beginAtZero: false,
        min: 2000,
      },
    },
  };

  return (
    <div style={{ padding: "20px", maxWidth: "600px" }}>
      <Line data={data} options={options} />
    </div>
  );
};

export default ChartStatsHistoriqueWar;

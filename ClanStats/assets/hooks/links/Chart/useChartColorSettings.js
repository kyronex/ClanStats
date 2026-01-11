import { useState } from "react";

const useChartColorSettings = () => {
  //const [startHue] = useState(() => Math.random() * 360);
  const [startHue] = useState(175);

  const createColorSetting = (r, g, b) => ({
    radar: {
      backgroundColor: `rgba(${r}, ${g}, ${b}, 0.3)`,
      borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      borderWidth: 3,
      pointBackgroundColor: `rgba(${r}, ${g}, ${b}, 1)`,
      pointBorderColor: "#fff",
      pointHoverBackgroundColor: "#fff",
      pointHoverBorderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      fill: true,
    },
    bar: {
      backgroundColor: `rgba(${r}, ${g}, ${b}, 0.45)`,
      borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      borderWidth: 2,
      hoverBackgroundColor: `rgba(${r}, ${g}, ${b}, 0.65)`,
      hoverBorderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      hoverBorderWidth: 3,
    },
    line: {
      backgroundColor: `rgba(${r}, ${g}, ${b}, 0.15)`,
      borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      borderWidth: 2,
      pointBackgroundColor: `rgba(${r}, ${g}, ${b}, 1)`,
      pointBorderColor: "#fff",
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointHoverBackgroundColor: "#fff",
      pointHoverBorderColor: `rgba(${r}, ${g}, ${b}, 1)`,
      pointHoverBorderWidth: 2,
      tension: 0.3,
      fill: false,
      backgroundColor: "transparent",
      // Version avec remplissage (area chart)
      //fill: true,
      //backgroundColor: `rgba(${r}, ${g}, ${b}, 0.15)`,
    },
    raw: {
      rgb: `rgb(${r}, ${g}, ${b})`,
      rgba: (alpha = 1) => `rgba(${r}, ${g}, ${b}, ${alpha})`,
    },
  });

  const createColorSettingHSL = (hue, saturation = 70, lightness = 50) => ({
    radar: {
      backgroundColor: `hsla(${hue}, ${saturation}%, ${lightness}%, 0.3)`,
      borderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      borderWidth: 3,
      pointBackgroundColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      pointBorderColor: "#fff",
      pointHoverBackgroundColor: "#fff",
      pointHoverBorderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      fill: true,
    },
    bar: {
      backgroundColor: `hsla(${hue}, ${saturation}%, ${lightness}%, 0.45)`,
      borderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      borderWidth: 2,
      hoverBackgroundColor: `hsla(${hue}, ${saturation}%, ${lightness}%, 0.65)`,
      hoverBorderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      hoverBorderWidth: 3,
    },
    line: {
      backgroundColor: "transparent",
      borderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      borderWidth: 2,
      pointBackgroundColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      pointBorderColor: "#fff",
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointHoverBackgroundColor: "#fff",
      pointHoverBorderColor: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      pointHoverBorderWidth: 2,
      tension: 0.3,
      fill: false,
    },
    raw: {
      hsl: `hsl(${hue}, ${saturation}%, ${lightness}%)`,
      hsla: (alpha = 1) => `hsla(${hue}, ${saturation}%, ${lightness}%, ${alpha})`,
    },
  });

  const COLOR_SETTINGS = {
    SETTINGS_0: createColorSetting(54, 162, 235), // 🔵 Bleu
    SETTINGS_1: createColorSetting(255, 99, 132), // 🔴 Rouge/Rose
    SETTINGS_2: createColorSetting(75, 192, 192), // 🟢 Vert/Turquoise
    SETTINGS_3: createColorSetting(255, 206, 86), // 🟡 Jaune/Orange
    SETTINGS_4: createColorSetting(153, 102, 255), // 🟣 Violet/Mauve
  };

  const getColorSettingByIndex = (index, total, chartType = "line") => {
    const ORIGINAL_COLORS = [
      { hue: 206, saturation: 82, lightness: 57 },
      { hue: 347, saturation: 100, lightness: 69 },
      { hue: 180, saturation: 50, lightness: 52 },
      { hue: 43, saturation: 100, lightness: 67 },
      { hue: 260, saturation: 100, lightness: 70 },
    ];

    // Zones à éviter (±20° autour des originaux)
    const FORBIDDEN_ZONES = [206, 347, 180, 43, 260];

    const isNearForbidden = (hue) => {
      return FORBIDDEN_ZONES.some((zone) => {
        const diff = Math.abs(hue - zone);
        return Math.min(diff, 360 - diff) < 20;
      });
    };

    let hue, saturation, lightness;

    if (index < ORIGINAL_COLORS.length) {
      const original = ORIGINAL_COLORS[index];
      hue = original.hue;
      saturation = original.saturation;
      lightness = original.lightness;
    } else {
      // Génération avec décalage pour éviter les collisions
      let baseHue = (206 + (index * 360) / total) % 360;

      // Décale si trop proche d'une couleur originale
      while (isNearForbidden(baseHue)) {
        baseHue = (baseHue + 15) % 360;
      }

      hue = baseHue;

      const STYLES = {
        NEUTRE: { saturation: 60, lightness: 44 },
        NEON: { saturation: 100, lightness: 58 },
        PASTEL: { saturation: 40, lightness: 72 },
      };

      const ORDER = [STYLES.NEUTRE, STYLES.NEON, STYLES.PASTEL];
      const style = ORDER[index % 3];
      saturation = style.saturation;
      lightness = style.lightness;
    }

    const settings = createColorSettingHSL(hue, saturation, lightness);
    return settings[chartType] || settings.line;
  };

  return {
    COLOR_SETTINGS,
    createColorSetting,
    getColorSettingByIndex,
  };
};

export { useChartColorSettings };

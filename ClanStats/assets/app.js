import "./styles/app.css";
import ClanStatsContainer from "./containers/ClanStatsContainer";
import { createRoot } from "react-dom/client";

let root = null;
function render() {
  const container = document.getElementById("react-clan-search");
  if (!container) {
    console.warn("Container #react-clan-search non trouvé");
    return;
  }

  if (!root) {
    root = createRoot(container);
  }
  root.render(<ClanStatsContainer />);
}

// Premier rendu
render();
if (module.hot) {
  module.hot.accept("./containers/ClanStatsContainer", () => {
    console.log("🔥 HMR: ClanStatsContainer mis à jour");
    render();
  });
}

//import './styles/app.scss';
import "./styles/app.css";
import ClanSearchForm from "./components/ClanSearchForm.jsx";
import ClanSearchResult from "./components/ClanSearchResult.jsx";
import ClanRiverRaceLog from "./components/ClanRiverRaceLog.jsx";
import ClanInfo from "./components/ClanInfo.jsx";
import ClanHistoriqueWar from "./components/ClanHistoriqueWar.jsx";
import ClanStatsHistoriqueWar from "./components/ClanStatsHistoriqueWar.jsx";
import React, { useState, useEffect } from "react";

let createRoot;
try {
  createRoot = require("react-dom/client").createRoot;
} catch (e) {
  console.warn("react-dom/client non disponible, fallback vers react-dom");
  const ReactDOM = require("react-dom");
  createRoot = (container) => ({
    render: (el) => ReactDOM.render(el, container),
  });
}
function ClanStatsContainer() {
  const [searchResults, setSearchResults] = useState([]);
  const [selectedClan, setSelectedClan] = useState(null);
  const [activeMembers, setActiveMembers] = useState([]);
  const [exMembers, setExMembers] = useState([]);
  //const [taskId, setTaskId] = useState("dataTask_#QPJC0JG9_694c3f39861de3.20316030");
  const [taskId, setTaskId] = useState([]);

  useEffect(() => {
    console.log("⭐ useEffect app.js [activeMembers, exMembers]");
    console.log("📋 activeMembers:", activeMembers);
    console.log("📋 exMembers:", exMembers);
    console.log("📋 taskId:", taskId);
  }, [activeMembers, exMembers, taskId]);

  return (
    <div>
      <ClanSearchForm onSearchResults={setSearchResults} />

      {searchResults.length > 0 && <ClanSearchResult clans={searchResults} onClanSelect={setSelectedClan} />}

      {selectedClan && (
        <div>
          <ClanInfo clan={selectedClan} />
          <ClanRiverRaceLog clan={selectedClan} activeMembers={setActiveMembers} exMembers={setExMembers} taskId={setTaskId} />
        </div>
      )}

      {taskId.length > 0 && <ClanStatsHistoriqueWar taskId={taskId} />}
      {activeMembers.length > 0 && <ClanHistoriqueWar members={activeMembers} membersClan={true} />}
      {exMembers.length > 0 && <ClanHistoriqueWar members={exMembers} membersClan={false} />}
    </div>
  );
}

let rootClanSearch = null;
function render() {
  const mountClanSearch = document.getElementById("react-clan-search");
  if (mountClanSearch) {
    if (!rootClanSearch) {
      rootClanSearch = createRoot(mountClanSearch);
    }
    rootClanSearch.render(<ClanStatsContainer />);
  }
}
render();
if (module.hot) {
  console.log("🔥 HMR available");
  module.hot.accept((err) => {
    if (err) console.error("HMR error", err);
    else render();
  });
  // 🎯 ACCEPTER TOUT LE DOSSIER COMPONENTS
  module.hot.accept(require.context("./components", true, /\.(js|jsx)$/), () => {
    console.log("🔄 HMR: Component modifié dans ./components/ !");
    render();
  });
  module.hot.accept("./styles/app.css", () => console.log("🎨 CSS reloaded"));
}

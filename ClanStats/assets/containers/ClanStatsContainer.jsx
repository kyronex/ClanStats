import ClanSearchForm from "../components/ClanSearchForm";
import ClanSearchResult from "../components/ClanSearchResult";
import ClanRiverRaceLog from "../components/ClanRiverRaceLog";
import ClanInfo from "../components/ClanInfo";
import ClanHistoriqueWar from "../components/ClanHistoriqueWar";
import ClanStatsHistoriqueWar from "../components/ClanStatsHistoriqueWar";
import { useClanWorkflow } from "../hooks";

function ClanStatsContainer() {
  const {
    searchResults,
    selectedClan,
    activeMembers,
    exMembers,
    taskId,
    setSearchResults,
    setSelectedClan,
    setActiveMembers,
    setExMembers,
    setTaskId,
  } = useClanWorkflow();

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

export default ClanStatsContainer;

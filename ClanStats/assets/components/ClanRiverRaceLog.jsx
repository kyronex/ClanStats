import { useHistoriqueClanWar } from "../hooks";
import { useClanRiverRaceLog } from "../hooks";

import React, { useState, useEffect, useCallback, memo } from "react";

// TODO : CUSTOM HOOK pour toggle Set handleSelectedHistory , handleShowedRow , handleShowedMembers
function ClanRiverRaceLog({ clan = {}, activeMembers, exMembers }) {
  const hookHCW = useHistoriqueClanWar();
  const hookCRRL = useClanRiverRaceLog();

  const [riverRaceLogData, setRiverRaceLogData] = useState(null);

  const [selectAll, setSelectAll] = useState(false);
  const [warsSelected, setWarsSelected] = useState(new Set());
  const [showedRow, setShowedRow] = useState(new Set());

  const handleSelectAll = () => {
    if (!riverRaceLogData || riverRaceLogData.length === 0) {
      return;
    }
    const currentlyAllSelected = warsSelected.size === riverRaceLogData.length;
    if (currentlyAllSelected) {
      setSelectAll(false);
      setWarsSelected(new Set());
    } else {
      const allIds = riverRaceLogData.map((item) => `${item.seasonId}_${item.sectionIndex}`);
      setSelectAll(true);
      setWarsSelected(new Set(allIds));
    }
  };

  const handleSelectedHistory = useCallback((id) => {
    setWarsSelected((prev) => {
      const newSet = new Set(prev);
      newSet.has(id) ? newSet.delete(id) : newSet.add(id);
      return newSet;
    });
  }, []);

  const handleShowedRow = useCallback((id) => {
    setShowedRow((prev) => {
      const newSet = new Set(prev);
      newSet.has(id) ? newSet.delete(id) : newSet.add(id);
      return newSet;
    });
  }, []);

  useEffect(() => {
    if (riverRaceLogData && riverRaceLogData.length === warsSelected.size) {
      setSelectAll(true);
    } else {
      setSelectAll(false);
    }
  }, [warsSelected, riverRaceLogData]);

  const handleHistoriqueClanWar = async (e) => {
    e.preventDefault();
    if (warsSelected.size < 1) {
      console.warn({ general: "Veuillez choisir au moins une saison" });
      return;
    }
    const result = await hookHCW.historiqueClanWar(clan, warsSelected, activeMembers, exMembers);
    if (result.success) {
      console.log(`✅ Historique membres trouvés`);
      hookHCW.clearErrors();
    } else {
      console.log(`❌ Recherche échouée: ${result.message}`);
      hookHCW.clearErrors();
    }
  };

  useEffect(() => {
    let isCancelled = false;
    const fetchData = async () => {
      const result = await hookCRRL.clanRiverRaceLog(clan);
      if (isCancelled) {
        console.log("⏹️ Requête annulée, ignorer le résultat");
        return;
      }
      if (result.success) {
        setRiverRaceLogData(result.data);
        hookCRRL.clearErrors();
      } else {
        console.log(`❌ Recherche échouée: ${result.message}`);
        setRiverRaceLogData(null);
        hookCRRL.clearErrors();
      }
    };

    if (clan?.tag) {
      fetchData();
    }

    return () => {
      console.log("🛑 Annulation recherche clan");
      isCancelled = true;
    };
  }, [clan]);

  return (
    <div className="alert alert-success mt-3">
      {riverRaceLogData ? (
        <div>
          <table>
            <thead>
              <tr>
                <th colSpan="5">Nombre de résultat : {riverRaceLogData.length}</th>
              </tr>
              <tr>
                <th></th>
                <th>Season Id</th>
                <th>Section Index</th>
                <th>Created Date</th>
                <th>
                  <input type="checkbox" checked={selectAll} onChange={handleSelectAll} />
                  <button onClick={handleHistoriqueClanWar} type="button">
                    {hookHCW.isLoading ? "⏳ Chargement..." : "Historique"}
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              {riverRaceLogData?.map((riverRaceLog) => {
                const rowId = `${riverRaceLog.seasonId}_${riverRaceLog.sectionIndex}`;
                return (
                  <RiverRaceLog
                    key={rowId}
                    riverRaceLog={riverRaceLog}
                    rowId={rowId}
                    showedRow={showedRow}
                    warsSelected={warsSelected}
                    handleShowedRow={handleShowedRow}
                    handleSelectedHistory={handleSelectedHistory}
                  />
                );
              })}
            </tbody>
          </table>
        </div>
      ) : (
        <div>⏳ Chargement informations clan ...</div>
      )}
    </div>
  );
}

const RiverRaceLog = memo(function RiverRaceLog({ riverRaceLog, rowId, showedRow, warsSelected, handleShowedRow, handleSelectedHistory }) {
  const handleClick = () => handleShowedRow(rowId);
  const handleChange = () => handleSelectedHistory(rowId);

  return (
    <React.Fragment>
      <tr>
        <td>
          <button onClick={handleClick} type="button">
            {showedRow.has(rowId) ? "📂" : "📁"}
          </button>
        </td>
        <td>{riverRaceLog.seasonId}</td>
        <td>{riverRaceLog.sectionIndex}</td>
        <td>{riverRaceLog.createdDate}</td>
        <td>
          <input type="checkbox" value={rowId} checked={warsSelected.has(rowId)} onChange={handleChange} />
        </td>
      </tr>
      {showedRow.has(rowId) && (
        <TableClans key={rowId} riverRaceLog={riverRaceLog} rowId={rowId} handleShowedRow={handleShowedRow} showedRow={showedRow} />
      )}
    </React.Fragment>
  );
});

const TableClans = memo(function TableClans({ riverRaceLog, rowId, handleShowedRow, showedRow }) {
  return (
    <tr>
      <td colSpan="5">
        <table className="table table-sm clans-table">
          <thead>
            <tr>
              <th></th>
              <th>Name</th>
              <th>Tag</th>
              <th>Rank</th>
              <th>Trophy Change</th>
              <th>Fame</th>
              <th>Clan Score</th>
              <th>Finish Time</th>
              <th>Badge Id</th>
            </tr>
          </thead>
          <tbody>
            {riverRaceLog.clans?.map((clan) => {
              const rowIdClan = `${rowId}_${clan.tag}`;
              return (
                <TableClan key={rowIdClan} clan={clan} rowIdClan={rowIdClan} handleShowedRow={handleShowedRow} showedRow={showedRow} />
              );
            })}
          </tbody>
        </table>
      </td>
    </tr>
  );
});

const TableClan = memo(function TableClan({ clan, rowIdClan, handleShowedRow, showedRow }) {
  const handleClick = () => handleShowedRow(rowIdClan);
  return (
    <React.Fragment>
      <tr>
        <td>
          <button onClick={handleClick} type="button">
            {showedRow.has(rowIdClan) ? "➖" : "➕"}
          </button>
        </td>
        <td>{clan.name}</td>
        <td>{clan.tag}</td>
        <td>{clan.rank}</td>
        <td>{clan.trophyChange}</td>
        <td>{clan.fame}</td>
        <td>{clan.clanScore}</td>
        <td>{clan.finishTime}</td>
        <td>{clan.badgeId}</td>
      </tr>
      {showedRow.has(rowIdClan) && <TableParticipants participants={clan.participants} rowIdClan={rowIdClan} />}
    </React.Fragment>
  );
});

const TableParticipants = memo(function TableParticipants({ participants, rowIdClan }) {
  return (
    <tr>
      <td colSpan="9">
        <table className="table table-sm">
          <thead>
            <tr>
              <th>Name</th>
              <th>Tag</th>
              <th>Fame</th>
              <th>Boat Attacks</th>
              <th>Decks Used</th>
            </tr>
          </thead>
          <tbody>
            {participants?.map((participant) => {
              const rowIdClanPlayer = `${rowIdClan}_${participant.tag}`;
              return <ParticipantItem key={rowIdClanPlayer} participant={participant} />;
            })}
          </tbody>
        </table>
      </td>
    </tr>
  );
});

const ParticipantItem = memo(function ParticipantItem({ participant }) {
  return (
    <tr>
      <td>{participant.name}</td>
      <td>{participant.tag}</td>
      <td>{participant.fame}</td>
      <td>{participant.boatAttacks}</td>
      <td>{participant.decksUsed}</td>
    </tr>
  );
});

export default ClanRiverRaceLog;

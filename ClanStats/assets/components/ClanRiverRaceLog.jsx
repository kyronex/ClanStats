import { useHistoriqueClanWar } from "../hooks";
import { useClanRiverRaceLog } from "../hooks";
import { useToggleSet } from "../hooks";
import { useTableSort } from "../hooks";
import { BoutonSort } from "../hooks";

import React, { useState, useEffect, useCallback, memo } from "react";

function ClanRiverRaceLog({ clan, activeMembers, exMembers, taskId }) {
  const {
    historiqueClanWar,
    isLoading: isLoadingHCW,
    errors: errorsHCW,
    hasErrors: hasErrorsHCW,
    clearErrors: clearErrorsHCW,
  } = useHistoriqueClanWar();
  const {
    clanRiverRaceLog,
    isLoading: isLoadingCRRL,
    errors: errorsCRRL,
    hasErrors: hasErrorsCRRL,
    clearErrors: clearErrorsCRRL,
  } = useClanRiverRaceLog();

  const {
    toggle: handleSelectedHistory,
    has: isWarHistorySelected,
    set: warsSelected,
    replace: setWarsSelected,
    clear: clearWarsSelected,
  } = useToggleSet();
  const { toggle: handleShowedRow, has: isShowedRow, set: showedRow } = useToggleSet();

  const [riverRaceLogData, setRiverRaceLogData] = useState(null);

  const [selectAll, setSelectAll] = useState(false);

  const handleSelectAll = () => {
    if (!riverRaceLogData || riverRaceLogData.length === 0) {
      return;
    }
    const currentlyAllSelected = warsSelected.size === riverRaceLogData.length;
    if (currentlyAllSelected) {
      setSelectAll(false);
      clearWarsSelected();
    } else {
      const allIds = riverRaceLogData.map((item) => `${item.seasonId}_${item.sectionIndex}`);
      setSelectAll(true);
      setWarsSelected(new Set(allIds));
    }
  };

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
    const result = await historiqueClanWar(clan, warsSelected, activeMembers, exMembers, taskId);
    if (result.success) {
      clearErrorsHCW;
    } else {
      console.log(`❌ Recherche échouée: ${result.message}`);
      clearErrorsHCW;
    }
  };

  useEffect(() => {
    let isCancelled = false;
    const fetchData = async () => {
      const result = await clanRiverRaceLog(clan);
      if (isCancelled) {
        return;
      }
      if (result.success) {
        setRiverRaceLogData(result.data);
        clearErrorsCRRL;
      } else {
        console.log(`❌ Recherche échouée: ${result.message}`);
        setRiverRaceLogData(null);
        clearErrorsCRRL;
      }
    };

    if (clan?.tag) {
      fetchData();
    }

    return () => {
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
                    {isLoadingHCW ? "⏳ Chargement..." : "Historique"}
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
                    isShowedRow={isShowedRow}
                    isWarHistorySelected={isWarHistorySelected}
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

const RiverRaceLog = memo(function RiverRaceLog({
  riverRaceLog,
  rowId,
  isShowedRow,
  isWarHistorySelected,
  handleShowedRow,
  handleSelectedHistory,
}) {
  const handleClick = () => handleShowedRow(rowId);
  const handleChange = () => handleSelectedHistory(rowId);

  return (
    <React.Fragment>
      <tr>
        <td>
          <button onClick={handleClick} type="button">
            {isShowedRow(rowId) ? "📂" : "📁"}
          </button>
        </td>
        <td>{riverRaceLog.seasonId}</td>
        <td>{riverRaceLog.sectionIndex}</td>
        <td>{riverRaceLog.createdDate}</td>
        <td>
          <input type="checkbox" value={rowId} checked={isWarHistorySelected(rowId)} onChange={handleChange} />
        </td>
      </tr>
      {isShowedRow(rowId) && (
        <TableClans key={rowId} riverRaceLog={riverRaceLog} rowId={rowId} handleShowedRow={handleShowedRow} isShowedRow={isShowedRow} />
      )}
    </React.Fragment>
  );
});

const TableClans = memo(function TableClans({ riverRaceLog, rowId, handleShowedRow, isShowedRow }) {
  const [keysSort] = useState({
    name: "Nom",
    tag: "Tag",
    rank: "Rank",
    trophyChange: "Trophy Change",
    fame: "Fame",
    clanScore: "Clan Score",
    finishTime: "Finish Time",
    badgeId: "Badge Id",
  });

  const { tabConfSort, sortedData, handleWaySorts, handleResetSorts, handleEnabledSorts, handleShowTabConfSorts } = useTableSort(
    keysSort,
    riverRaceLog.clans
  );

  return (
    <tr>
      <td colSpan="5">
        <table className="table table-sm clans-table">
          <thead>
            <tr>
              <th>plop</th>
              {Object.entries(keysSort).map(([key, label]) => (
                <th key={key}>
                  {label} <br />
                  <BoutonSort cle={key} handleEnabledSorts={handleEnabledSorts} handleWaySorts={handleWaySorts} tabConfSort={tabConfSort} />
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {sortedData?.map((clan, index) => {
              const rowIdClan = `${rowId}_${clan.tag}-${index}`;
              return (
                <TableClan key={rowIdClan} clan={clan} rowIdClan={rowIdClan} handleShowedRow={handleShowedRow} isShowedRow={isShowedRow} />
              );
            })}
          </tbody>
        </table>
      </td>
    </tr>
  );
});

const TableClan = memo(function TableClan({ clan, rowIdClan, handleShowedRow, isShowedRow }) {
  const handleClick = () => handleShowedRow(rowIdClan);
  return (
    <React.Fragment>
      <tr>
        <td>
          <button onClick={handleClick} type="button">
            {isShowedRow(rowIdClan) ? "➖" : "➕"}
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
      {isShowedRow(rowIdClan) && <TableParticipants participants={clan.participants} rowIdClan={rowIdClan} />}
    </React.Fragment>
  );
});

const TableParticipants = memo(function TableParticipants({ participants, rowIdClan }) {
  const [keysSort] = useState({
    name: "Nom",
    tag: "Tag",
    fame: "Fame",
    boatAttacks: "Boat Attacks",
    decksUsed: "Decks Used",
  });

  const { tabConfSort, sortedData, handleWaySorts, handleResetSorts, handleEnabledSorts, handleShowTabConfSorts } = useTableSort(
    keysSort,
    participants
  );

  return (
    <tr>
      <td colSpan="9">
        <table className="table table-sm">
          <thead>
            <tr>
              {Object.entries(keysSort).map(([key, label]) => (
                <th key={key}>
                  {label} <br />
                  <BoutonSort cle={key} handleEnabledSorts={handleEnabledSorts} handleWaySorts={handleWaySorts} tabConfSort={tabConfSort} />
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {sortedData?.map((participant, index) => {
              const rowIdClanPlayer = `${rowIdClan}_${participant.tag}-${index}`;
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

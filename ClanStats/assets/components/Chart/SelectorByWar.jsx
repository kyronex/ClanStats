import { useToggleSet } from "../../hooks";
import { useChartPlayerByWar } from "../../hooks";
import React, { memo, useEffect, useRef } from "react";

const SelectorByWar = ({ warsStats, playersAnalysisStats, handlePlayersSelect, handleWarSelect }) => {
  playersAnalysisStats = Object.entries(playersAnalysisStats);
  const {
    toggle: handleSelectedPlayer,
    has: isPlayersSelected,
    set: playersSelected,
    replace: replacePlayersSelected,
    hash: playersSelectedHash,
  } = useToggleSet([], { maxSize: 5 });
  const { toggle: handleSelectedWar, has: isWarsSelected, set: warsSelected, hash: warsSelectedHash } = useToggleSet([], { maxSize: 1 });
  const { currentWar, selectablePlayers, filteredPlayers, getValidSelectedPlayers } = useChartPlayerByWar(
    warsSelected,
    playersSelected,
    warsStats,
    playersAnalysisStats
  );
  const prevHashsRef = useRef({ wars: "", players: "" });

  useEffect(() => {
    if (!currentWar) return;
    const validPlayersSet = getValidSelectedPlayers(playersSelected);
    const needsSync = validPlayersSet.size !== playersSelected.size || ![...validPlayersSet].every((tag) => playersSelected.has(tag));
    if (needsSync) {
      replacePlayersSelected([...validPlayersSet]);
    }
  }, [currentWar, playersSelected, getValidSelectedPlayers, replacePlayersSelected]);

  useEffect(() => {
    if (warsSelected.size === 0) return;
    if (playersSelected.size === 0) return;
    if (Object.keys(filteredPlayers).length === 0) return;
    const hashesChanged = prevHashsRef.current.wars !== warsSelectedHash || prevHashsRef.current.players !== playersSelectedHash;
    if (!hashesChanged) return;
    prevHashsRef.current = { wars: warsSelectedHash, players: playersSelectedHash };
    handlePlayersSelect(filteredPlayers);
    handleWarSelect(warsSelected);
  }, [playersSelectedHash, warsSelectedHash, filteredPlayers]);

  return (
    <React.Fragment>
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
          gap: "10px",
          padding: "10px",
        }}
      >
        {Object.entries(warsStats)
          .filter(([key]) => key.match(/^\d+_\d+$/))
          .map(([warKey, warData], index) => {
            return (
              <WarItem key={warKey} rowId={warKey} war={warData} isWarsSelected={isWarsSelected} handleSelectedWar={handleSelectedWar} />
            );
          })}
      </div>
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
          gap: "10px",
          padding: "10px",
        }}
      >
        {Object.entries(selectablePlayers)?.map(([playerTag, playerData], index) => {
          return (
            <PlayerItem
              key={playerTag}
              rowId={playerTag}
              player={playerData}
              isPlayersSelected={isPlayersSelected}
              handleSelectedPlayer={handleSelectedPlayer}
            />
          );
        })}
      </div>
    </React.Fragment>
  );
};

const WarItem = memo(function WarItem({ rowId, war, isWarsSelected, handleSelectedWar }) {
  const handleChange = () => handleSelectedWar(rowId);
  return (
    <div>
      <span>{war.sessionId}</span>
      <input type="checkbox" value={rowId} checked={isWarsSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

const PlayerItem = memo(function PlayerItem({ rowId, player, isPlayersSelected, handleSelectedPlayer }) {
  const handleChange = () => handleSelectedPlayer(rowId);
  return (
    <div>
      <span>{player.originalStats.name}</span>
      <input type="checkbox" value={rowId} checked={isPlayersSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export default SelectorByWar;

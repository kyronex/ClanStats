import React, { memo } from "react";

const SelectorComparePlayers = ({
  warsStats,
  playersAnalysisStats,
  isWarsSelected,
  handleSelectedWar,
  isPlayersSelected,
  handleSelectedPlayer,
}) => {
  playersAnalysisStats = Object.entries(playersAnalysisStats);
  warsStats = Object.entries(warsStats);
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
        {warsStats
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
        {playersAnalysisStats.map(([playerTag, playerData], index) => {
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

export default SelectorComparePlayers;

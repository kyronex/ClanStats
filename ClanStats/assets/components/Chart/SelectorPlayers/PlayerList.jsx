import { useSelectorPlayersContext } from "../../../hooks";
import { PlayerItem } from "../../../components";

function PlayersList() {
  const {
    currentWar,
    maxPlayers,
    selectablePlayers,
    isPlayersSelected,
    handleSelectedPlayer,
    enablePlayerSelectAll,
    selectAllPlayers,
    handleSelectAllPlayers,
  } = useSelectorPlayersContext();

  const players = Object.entries(selectablePlayers);

  return (
    <div
      style={{
        display: "grid",
        gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
        gap: "10px",
        padding: "10px",
      }}
    >
      {enablePlayerSelectAll && currentWar && (
        <div>
          <label>
            <input type="checkbox" checked={selectAllPlayers} onChange={handleSelectAllPlayers} />
            <span>
              Tous
              {maxPlayers && ` (max ${maxPlayers})`}
              {` (${Object.keys(selectablePlayers).length})`}
            </span>
          </label>
        </div>
      )}
      {players?.map(([playerTag, playerData], index) => {
        return (
          <PlayerItem
            key={playerTag}
            rowId={playerTag}
            playerData={playerData}
            isPlayersSelected={isPlayersSelected}
            handleSelectedPlayer={handleSelectedPlayer}
          />
        );
      })}
    </div>
  );
}

export { PlayersList };

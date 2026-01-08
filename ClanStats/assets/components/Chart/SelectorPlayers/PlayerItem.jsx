import { memo } from "react";

const PlayerItem = memo(function PlayerItem({ rowId, playerData, isPlayersSelected, handleSelectedPlayer }) {
  const handleChange = () => handleSelectedPlayer(rowId);
  return (
    <div>
      <span>{playerData.originalStats.name}</span>
      <input type="checkbox" value={rowId} checked={isPlayersSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export { PlayerItem };

import { memo } from "react";
import { PlayerStats } from "../../../types";

type PlayerItemProps = {
  rowId: string;
  playerData: PlayerStats;
  isPlayersSelected: (rowId: string) => boolean;
  handleSelectedPlayer: (rowId: string) => void;
};

const PlayerItem = memo(function PlayerItem({ rowId, playerData, isPlayersSelected, handleSelectedPlayer }: PlayerItemProps) {
  const handleChange = () => handleSelectedPlayer(rowId);
  return (
    <div>
      <span>{playerData.originalStats.name}</span>
      <input type="checkbox" value={rowId} checked={isPlayersSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export { PlayerItem };

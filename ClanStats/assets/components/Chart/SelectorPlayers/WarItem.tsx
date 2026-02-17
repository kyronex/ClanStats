import { memo } from "react";
import { WarStatsHistoriqueClanWar } from "../../../types";

type WarItemProps = {
  rowId: string;
  warData: WarStatsHistoriqueClanWar;
  isWarsSelected: (rowId: string) => boolean;
  handleSelectedWar: (rowId: string) => void;
};
const WarItem = memo(function WarItem({ rowId, warData, isWarsSelected, handleSelectedWar }: WarItemProps) {
  const handleChange = () => handleSelectedWar(rowId);
  return (
    <div>
      <span>{warData.sessionId}</span>
      <input type="checkbox" value={rowId} checked={isWarsSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export { WarItem };

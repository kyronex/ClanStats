import { memo } from "react";

const WarItem = memo(function WarItem({ rowId, warData, isWarsSelected, handleSelectedWar }) {
  const handleChange = () => handleSelectedWar(rowId);
  return (
    <div>
      <span>{warData.sessionId}</span>
      <input type="checkbox" value={rowId} checked={isWarsSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export { WarItem };

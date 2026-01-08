import { useSelectorPlayersContext } from "../../../hooks";
import { WarItem } from "../../../components";

function WarsList() {
  const { warsStats, isWarsSelected, handleSelectedWar, selectAllWars, handleSelectAllWars, enableWarSelectAll, maxWars } =
    useSelectorPlayersContext();

  const wars = Object.entries(warsStats).filter(([key]) => key.match(/^\d+_\d+$/));

  return (
    <div
      style={{
        display: "grid",
        gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
        gap: "10px",
        padding: "10px",
      }}
    >
      {enableWarSelectAll && (
        <div>
          <label>
            <input type="checkbox" checked={selectAllWars} onChange={handleSelectAllWars} />
            <span>Toutes {maxWars ? `(max ${maxWars})` : ""}</span>
          </label>
        </div>
      )}
      {wars.map(([warKey, warData]) => (
        <WarItem key={warKey} rowId={warKey} warData={warData} isWarsSelected={isWarsSelected} handleSelectedWar={handleSelectedWar} />
      ))}
    </div>
  );
}

export { WarsList };

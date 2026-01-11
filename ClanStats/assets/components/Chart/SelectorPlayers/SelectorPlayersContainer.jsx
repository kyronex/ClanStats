import { SelectorPlayersContext, useSelectorPlayers } from "../../../hooks";
function SelectorPlayersContainer({
  children,
  warsStats,
  playersAnalysisStats,
  handleWarsSelect,
  handlePlayersSelect,
  maxPlayers = null,
  maxWars = 1,
  enablePlayerSelectAll = false,
  enableWarSelectAll = false,
}) {
  const { value } = useSelectorPlayers({
    warsStats,
    playersAnalysisStats,
    handleWarsSelect,
    handlePlayersSelect,
    maxPlayers,
    maxWars,
    enablePlayerSelectAll,
    enableWarSelectAll,
  });

  return <SelectorPlayersContext.Provider value={value}>{children}</SelectorPlayersContext.Provider>;
}

export { SelectorPlayersContainer };

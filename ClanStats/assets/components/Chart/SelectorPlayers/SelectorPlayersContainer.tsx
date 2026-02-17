import { ReactNode } from "react";
import { SelectorPlayersContext, useSelectorPlayers } from "../../../hooks";
import { WarStatsHistoriqueClanWar, PlayerStats } from "../../../types";

type SelectorPlayersContainerProps = {
  children: ReactNode;
  warsStats: { [key: string]: WarStatsHistoriqueClanWar };
  playersAnalysisStats: { [key: string]: PlayerStats };
  handleWarsSelect: (wars: Set<string> | null) => void;
  handlePlayersSelect: (players: { [key: string]: PlayerStats }) => void;
  maxWars: number;
  maxPlayers: number;
  enablePlayerSelectAll: boolean;
  enableWarSelectAll: boolean;
};

function SelectorPlayersContainer({
  children,
  warsStats,
  playersAnalysisStats,
  handleWarsSelect,
  handlePlayersSelect,
  maxWars = 1,
  maxPlayers = null,
  enableWarSelectAll = false,
  enablePlayerSelectAll = false,
}: SelectorPlayersContainerProps) {
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

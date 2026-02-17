import { createContext, useContext } from "react";
import { SelectorPlayersContextValue } from "../../../../types";

const SelectorPlayersContext = createContext<SelectorPlayersContextValue | null>(null);

function useSelectorPlayersContext() {
  const context = useContext(SelectorPlayersContext);
  if (!context) {
    throw new Error("useSelectorPlayersContext must be used within SelectorPlayersContainer");
  }
  return context;
}

export { SelectorPlayersContext, useSelectorPlayersContext };

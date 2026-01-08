import { createContext, useContext } from "react";

const SelectorPlayersContext = createContext(null);

function useSelectorPlayersContext() {
  const context = useContext(SelectorPlayersContext);
  if (!context) {
    throw new Error("useSelectorPlayersContext must be used within SelectorPlayersContainer");
  }
  return context;
}

export { SelectorPlayersContext, useSelectorPlayersContext };

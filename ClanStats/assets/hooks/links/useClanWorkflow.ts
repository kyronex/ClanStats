import { useState, useEffect } from "react";
import { ClanSearch } from "../../types";

const useClanWorkflow = () => {
  const [searchResults, setSearchResults] = useState<ClanSearch[]>([]);
  const [selectedClan, setSelectedClan] = useState<ClanSearch>(null);
  const [activeMembers, setActiveMembers] = useState([]);
  const [exMembers, setExMembers] = useState([]);
  const [taskId, setTaskId] = useState("");
  //const [taskId, setTaskId] = useState("dataTask_#QPJC0JG9_697b18fa64a3e8.25042039");

  // Debug dev uniquement
  useEffect(() => {
    console.log("📋 Workflow:", { activeMembers, exMembers, taskId });
  }, [activeMembers, exMembers, taskId]);

  return {
    // État
    searchResults,
    selectedClan,
    activeMembers,
    exMembers,
    taskId,
    // Actions
    setSearchResults,
    setSelectedClan,
    setActiveMembers,
    setExMembers,
    setTaskId,
  };
};
export { useClanWorkflow };

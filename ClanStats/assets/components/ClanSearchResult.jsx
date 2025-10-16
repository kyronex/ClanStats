import React from "react";

function ClanSearchResult({ clans = [], onClanSelect }) {
  if (!clans || clans.length === 0) {
    return (
      <div>
        <p>Aucun clan trouvé.</p>
      </div>
    );
  }

  // 🎯 Gestion de la sélection d'un clan
  const handleSelectClan = (clan) => {
    if (onClanSelect) {
      onClanSelect(clan);
    }
  };

  return (
    <div>
      <h3>Résultats de recherche ({clans.length} clan(s) trouvé(s))</h3>
      <table border="1">
        <thead>
          <tr>
            <th>🏰 Nom</th>
            <th>🏷️ Tag</th>
            <th>🏆 Score</th>
            <th>⚔️ Trophées</th>
            <th>🎁 Donations</th>
            <th>👥 Membres</th>
            <th>🎯 Action</th>
          </tr>
        </thead>
        <tbody>
          {clans.map((clan) => (
            <tr key={clan.tag}>
              <td>{clan.name}</td>
              <td>#{clan.tag}</td>
              <td>{clan.clanScore ? clan.clanScore.toLocaleString() : "N/A"}</td>
              <td>{clan.clanWarTrophies ? clan.clanWarTrophies.toLocaleString() : "N/A"}</td>
              <td>{clan.donationsPerWeek ? clan.donationsPerWeek.toLocaleString() : "N/A"}</td>
              <td>
                {clan.members}/{clan.type === "open" ? "50" : "50"}
              </td>
              <td>
                <button onClick={() => handleSelectClan(clan)} type="button">
                  ✅ Sélectionner
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default ClanSearchResult;

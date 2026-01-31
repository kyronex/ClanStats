import { BoutonSort } from "../components";
import { useTableSort } from "../hooks";
import { ClanSearch, SortLabels } from "../types";

type ClanSearchResultProps = {
  clans: ClanSearch[];
  onClanSelect: (clan: ClanSearch) => void;
};

function ClanSearchResult({ clans, onClanSelect }: ClanSearchResultProps) {
  if (!clans || clans.length === 0) {
    return (
      <div>
        <p>Aucun clan trouvé.</p>
      </div>
    );
  }
  const SORT_LABELS: SortLabels = {
    name: "🏰 Nom",
    tag: "🏷️ Tag",
    clanScore: "🏆 Score",
    clanWarTrophies: "⚔️ Trophées",
    donationsPerWeek: "🎁 Donations",
    members: "👥 Membres",
  };

  const { tabConfSort, sortedData, handleWaySorts, handleResetSorts, handleEnabledSorts, handleShowTabConfSorts } = useTableSort(
    SORT_LABELS,
    clans,
  );

  // 🎯 Gestion de la sélection d'un clan
  const handleSelectClan = (clan: ClanSearch) => {
    if (onClanSelect) {
      onClanSelect(clan);
    }
  };

  return (
    <div>
      <h3>Résultats de recherche ({clans.length} clan(s) trouvé(s))</h3>
      <table border={1}>
        <thead>
          <tr>
            {Object.entries(SORT_LABELS).map(([key, label]) => (
              <th key={key}>
                {label} <br />
                <BoutonSort cle={key} handleEnabledSorts={handleEnabledSorts} handleWaySorts={handleWaySorts} tabConfSort={tabConfSort} />
              </th>
            ))}

            <th>
              🎯 Action <br /> <button onClick={handleResetSorts}>Reset Sort</button>
            </th>
          </tr>
        </thead>
        <tbody>
          {sortedData.map((clan, index) => (
            <tr key={`${clan.tag}-${index}`}>
              <td>{clan.name}</td>
              <td>{clan.tag}</td>
              <td>{clan.clanScore ? clan.clanScore.toLocaleString() : "N/A"}</td>
              <td>{clan.clanWarTrophies ? clan.clanWarTrophies.toLocaleString() : "N/A"}</td>
              <td>{clan.donationsPerWeek ? clan.donationsPerWeek.toLocaleString() : "N/A"}</td>
              <td>{clan.members}/50</td>
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

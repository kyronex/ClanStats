import { useClanInfo } from "../hooks";
import { useTableSort } from "../hooks";
import { BoutonSort } from "../hooks";

import React, { useState, useEffect } from "react";

function ClanInfo({ clan }) {
  const [clanData, setClanData] = useState({
    membersList: [],
  });
  const [showClanMembers, setShowClanMembers] = useState(false);
  const { clanInfo, isLoading, errors, hasErrors, clearErrors } = useClanInfo();

  const [keysSort] = useState({
    name: "Nom",
    tag: "Tag",
    expLevel: "Niveau",
    trophies: "Trophées",
    role: "Rôle",
    clanRank: "Rang",
    previousClanRank: "Rang Précédent",
    donations: "Dons",
    donationsReceived: "Dons reçus",
    lastSeen: "Dernière Connexion",
  });

  const { tabConfSort, sortedData, handleWaySorts, handleResetSorts, handleEnabledSorts, handleShowTabConfSorts } = useTableSort(
    keysSort,
    clanData.membersList
  );

  useEffect(() => {
    let isCancelled = false;
    const fetchData = async () => {
      const result = await clanInfo(clan);
      if (isCancelled) {
        console.log("⏹️ Requête annulée, ignorer le résultat");
        return;
      }
      if (result.success) {
        console.log(`✅ ${result.data.name} clan trouvés`);
        setClanData(result.data);
        clearErrors();
      } else {
        console.log(`❌ Recherche échouée: ${result.message}`);
        setClanData(null);
      }
    };
    if (clan?.tag) {
      fetchData();
    }
    return () => {
      console.log("🛑 Annulation recherche clan");
      isCancelled = true;
    };
  }, [clan, clanInfo]);

  return (
    <div className="alert alert-success mt-3">
      {clanData ? (
        <>
          <table id="clan-info-table" className="table table-striped">
            <thead>
              <tr>
                <th colSpan="2">Informations du Clan</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>Nom</th>
                <td>{clanData.name}</td>
              </tr>
              <tr>
                <th>Tag</th>
                <td>{clanData.tag}</td>
              </tr>
              <tr>
                <th>Description</th>
                <td>{clanData.description}</td>
              </tr>
              <tr>
                <th>Nombre de membres</th>
                <td>{clanData.members}</td>
              </tr>
              <tr>
                <th>Score</th>
                <td>{clanData.clanScore}</td>
              </tr>
              <tr>
                <th>Donations par semaine</th>
                <td>{clanData.donationsPerWeek}</td>
              </tr>
              <tr>
                <th>Statut</th>
                <td>{clanData.type}</td>
              </tr>
              <tr>
                <th>Trophées requis</th>
                <td>{clanData.requiredTrophies}</td>
              </tr>
              <tr>
                <th>Score de la guerre</th>
                <td>{clanData.clanWarTrophies}</td>
              </tr>
              <tr
                className="table-active cursor-pointer user-select-none"
                onClick={() => setShowClanMembers(!showClanMembers)}
                style={{ cursor: "pointer" }}
              >
                <td>
                  <span className="me-2">{showClanMembers ? "📂" : "📁"}</span>
                </td>
                <td>
                  <strong>Membres du clan</strong>
                  <span className="text-muted ms-2">
                    ({clanData.members} membres • {showClanMembers ? "Cliquez pour masquer" : "Cliquez pour afficher"})
                  </span>
                </td>
              </tr>
              {showClanMembers && (
                <tr>
                  <td colSpan="2">
                    <table className="table table-sm members-table">
                      <thead>
                        <tr>
                          {Object.entries(keysSort).map(([key, label]) => (
                            <th key={key}>
                              {label} <br />
                              <BoutonSort
                                cle={key}
                                handleEnabledSorts={handleEnabledSorts}
                                handleWaySorts={handleWaySorts}
                                tabConfSort={tabConfSort}
                              />
                            </th>
                          ))}
                        </tr>
                      </thead>
                      <tbody>
                        {sortedData?.map((member, index) => (
                          <tr key={`${member.tag}-${index}`}>
                            <td>{member.name}</td>
                            <td>{member.tag}</td>
                            <td>{member.expLevel}</td>
                            <td>{member.trophies}</td>
                            <td>{member.role}</td>
                            <td>{member.clanRank}</td>
                            <td>{member.previousClanRank}</td>
                            <td>{member.donations}</td>
                            <td>{member.donationsReceived}</td>
                            <td>{member.lastSeen}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </>
      ) : (
        <div>⏳ Chargement informations clan ...</div>
      )}
    </div>
  );
}

export default ClanInfo;

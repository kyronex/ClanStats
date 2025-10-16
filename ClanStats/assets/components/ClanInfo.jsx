import { useClanInfo } from "../hooks";
import React, { useState, useEffect } from "react";

function ClanInfo({ clan = {} }) {
  const [clanData, setClanData] = useState(null);
  const [showClanMembers, setShowClanMembers] = useState(false);
  const { clanInfo, isLoading, errors, hasErrors, clearErrors } = useClanInfo();

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
                          <th>Nom</th>
                          <th>Tag</th>
                          <th>Niveau</th>
                          <th>Trophées</th>
                          <th>Rôle</th>
                          <th>Rang</th>
                          <th>Rang Précédent</th>
                          <th>Dons</th>
                          <th>Dons reçus</th>
                          <th>Dernière Connexion</th>
                        </tr>
                      </thead>
                      <tbody>
                        {clanData.membersList?.map((member) => (
                          <tr key={member.tag}>
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

import React, { useState, useEffect, useRef, useCallback, memo } from "react";

// TODO : CUSTOM HOOK pour toggle Set handleSelectedHistory , handleShowedRow , handleShowedMembers

function ClanHistoriqueWar({ members = [], membersClan = [] }) {
  const componentRef = useRef(null);
  const [showedMembers, setShowedMembers] = useState(new Set());

  useEffect(() => {
    if (componentRef.current) {
      componentRef.current.scrollIntoView({ behavior: "smooth" });
      componentRef.current.focus();
    }
  }, []);

  const handleShowedMembers = useCallback((id) => {
    setShowedMembers((prev) => {
      const newSet = new Set(prev);
      newSet.has(id) ? newSet.delete(id) : newSet.add(id);
      return newSet;
    });
  }, []);

  return (
    <div ref={componentRef} tabIndex={-1}>
      <table className="table table-striped">
        <thead className="table-dark">
          <tr>
            <th colSpan="9">Historique des guerres</th>
          </tr>
          <tr>
            <th colSpan="9">
              {membersClan ? "Membres actifs :" : "Ex membres :"} {members.length}
            </th>
          </tr>
          <tr>
            <th>Name</th>
            <th>Participations Totales</th>
            <th>Total Fame</th>
            <th>Total Boats Attacked</th>
            <th>Total Decks Used</th>
            <th>Average Fame</th>
            <th>Average Boats Attacked</th>
            <th>Average Decks Used</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {members?.map((member) => {
            return (
              <MembersTable key={member.tag} member={member} handleShowedMembers={handleShowedMembers} showedMembers={showedMembers} />
            );
          })}
        </tbody>
      </table>
    </div>
  );
}

const MembersTable = memo(function MembersTable({ member, handleShowedMembers, showedMembers }) {
  const handleClick = () => handleShowedMembers(member.tag);
  return (
    <React.Fragment>
      <tr>
        <td>{member.name}</td>
        <td>{member.totalWarsParticipated}</td>
        <td>{member.totalWarsFame}</td>
        <td>{member.totalWarsBoatAttacks}</td>
        <td>{member.totalWarsDecksUsed}</td>
        <td>{member.averageWarsFame}</td>
        <td>{member.averageWarsBoatAttacks}</td>
        <td>{member.averageWarsDecksUsed}</td>
        <td>
          <button onClick={handleClick} type="button">
            {showedMembers.has(member.tag) ? "📂" : "📁"}
          </button>
        </td>
      </tr>
      {showedMembers.has(member.tag) && <MemberTable member={member} />}
    </React.Fragment>
  );
});

const MemberTable = memo(function MemberTable({ member }) {
  console.log("🔄 MemberTable render pour:", member.name);
  return (
    <tr>
      <td colSpan="9">
        <table className="table table-sm">
          <thead>
            <tr colSpan="9">
              <th>Session Id</th>
              <th>Fame</th>
              <th>Boat Attacks</th>
              <th>Decks Used</th>
            </tr>
          </thead>
          <tbody>
            {member.wars?.map((war) => {
              return <WarItem key={war.warId} war={war} />;
            })}
          </tbody>
        </table>
      </td>
    </tr>
  );
});

const WarItem = memo(function WarItem({ war }) {
  console.log("🔄 WarItem render pour:", war.warId);
  return (
    <tr>
      <td>{war.warId}</td>
      <td>{war.fame}</td>
      <td>{war.boatAttacks}</td>
      <td>{war.decksUsed}</td>
    </tr>
  );
});

export default ClanHistoriqueWar;

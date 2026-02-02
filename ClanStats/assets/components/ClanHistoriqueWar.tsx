import React, { useState, useEffect, useRef, memo } from "react";
import { BoutonSort } from "../components";
import { useToggleSet, useTableSort } from "../hooks";
import type { HistoriqueClanWar, War } from "../types";

type ClanHistoriqueWarProps = {
  members: HistoriqueClanWar[];
  membersClan: boolean;
};

function ClanHistoriqueWar({ members, membersClan }: ClanHistoriqueWarProps) {
  const componentRef = useRef(null);
  const { toggle: handleShowedMembers, has: isShowedMember } = useToggleSet();

  const [keysSort] = useState({
    tag: "Tag",
    name: "Name",
    totalWarsParticipated: "Participations Totales",
    totalWarsFame: "Total Fame",
    totalWarsBoatAttacks: "Total Boats Attacked",
    totalWarsDecksUsed: "Total Decks Used",
    averageWarsFame: "Average Fame",
    averageWarsBoatAttacks: "Average Boats Attacked",
    averageWarsDecksUsed: "Average Decks Used",
  });

  const { tabConfSort, sortedData, handleWaySorts, handleResetSorts, handleEnabledSorts } = useTableSort(keysSort, members);

  useEffect(() => {
    if (componentRef.current) {
      componentRef.current.scrollIntoView({ behavior: "smooth" });
      componentRef.current.focus();
    }
  }, []);

  return (
    <div ref={componentRef} tabIndex={-1}>
      <table className="table table-striped">
        <thead className="table-dark">
          <tr>
            <th colSpan={9}>Historique des guerres</th>
          </tr>
          <tr>
            <th colSpan={9}>
              {membersClan ? "Membres actifs :" : "Ex membres :"} {members.length}
            </th>
          </tr>
          <tr>
            {Object.entries(keysSort).map(([key, label]) => (
              <th key={key}>
                {label} <br />
                <BoutonSort cle={key} handleEnabledSorts={handleEnabledSorts} handleWaySorts={handleWaySorts} tabConfSort={tabConfSort} />
              </th>
            ))}
            <th>
              <button onClick={handleResetSorts}>Reset Sort</button>
            </th>
          </tr>
        </thead>
        <tbody>
          {sortedData?.map((member, index) => {
            return (
              <MembersTable
                key={`${member.tag}-${index}`}
                member={member}
                handleShowedMembers={handleShowedMembers}
                isShowedMember={isShowedMember}
              />
            );
          })}
        </tbody>
      </table>
    </div>
  );
}

type MembersTableProps = {
  member: HistoriqueClanWar;
  handleShowedMembers: (tag: string) => void;
  isShowedMember: (tag: string) => boolean;
};

const MembersTable = memo(function MembersTable({ member, handleShowedMembers, isShowedMember }: MembersTableProps) {
  const handleClick = () => handleShowedMembers(member.tag);
  return (
    <React.Fragment>
      <tr>
        <td>{member.tag}</td>
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
            {isShowedMember(member.tag) ? "📂" : "📁"}
          </button>
        </td>
      </tr>
      {isShowedMember(member.tag) && <MemberTable member={member} />}
    </React.Fragment>
  );
});

type MemberTableProps = {
  member: HistoriqueClanWar;
};

const MemberTable = memo(function MemberTable({ member }: MemberTableProps) {
  return (
    <tr>
      <td colSpan={9}>
        <table className="table table-sm">
          <thead>
            <tr>
              <th>Session Id</th>
              <th>Fame</th>
              <th>Boat Attacks</th>
              <th>Decks Used</th>
            </tr>
          </thead>
          <tbody>
            {member.wars?.map((war, index) => {
              return <WarItem key={`${war.warId}-${index}`} war={war} />;
            })}
          </tbody>
        </table>
      </td>
    </tr>
  );
});

type WarItemProps = {
  war: War;
};

const WarItem = memo(function WarItem({ war }: WarItemProps) {
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

import { useClanSearch } from "../hooks";
import React, { useState } from "react";

function ClanSearchForm({ onSearchResults }) {
  const { searchClans, isLoading, errors, hasErrors, clearErrors } = useClanSearch();
  const [formData, setFormData] = useState({
    nomClan: "",
    minMembers: "",
    maxMembers: "",
    minScore: "",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    if (hasErrors) {
      clearErrors();
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const result = await searchClans(formData, onSearchResults);
    if (result.success) {
      console.log(`✅ ${result.count} clans trouvés`);
    } else {
      console.log(`❌ Recherche échouée: ${result.message}`);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {hasErrors && (
        <div className="alert alert-danger">
          <ul>
            {errors.map((error, index) => (
              <li key={index}>{error}</li>
            ))}
          </ul>
        </div>
      )}

      <div className="form-group">
        <label htmlFor="nomClan">Nom Clan : </label>
        <input
          type="text"
          id="nomClan"
          name="nomClan"
          value={formData.nomClan}
          onChange={handleChange}
          placeholder="Entrez nom du clan"
          className="form-control"
          data-ajax-field="true"
          required
        />
        {errors.nomClan && <div className="invalid-feedback d-block">{errors.nomClan}</div>}
      </div>

      <div className="form-group">
        <label htmlFor="minMembers">Membres minimum :</label>
        <input
          type="number"
          id="minMembers"
          name="minMembers"
          value={formData.minMembers}
          onChange={handleChange}
          placeholder="Nombre de membres"
          className="form-control"
          data-ajax-field="true"
          min="2"
        />
        {errors.minMembers && <div className="invalid-feedback d-block">{errors.minMembers}</div>}
      </div>

      <div className="form-group">
        <label htmlFor="maxMembers">Membres maximum :</label>
        <input
          type="number"
          id="maxMembers"
          name="maxMembers"
          value={formData.maxMembers}
          onChange={handleChange}
          placeholder="Nombre de membres"
          className="form-control"
          data-ajax-field="true"
          max="50"
        />
        {errors.maxMembers && <div className="invalid-feedback d-block">{errors.maxMembers}</div>}
      </div>

      <div className="form-group">
        <label htmlFor="minScore">Score minimum :</label>
        <input
          type="number"
          id="minScore"
          name="minScore"
          value={formData.minScore}
          onChange={handleChange}
          placeholder="Score du clan"
          className="form-control"
          data-ajax-field="true"
          min="1"
        />
        {errors.minScore && <div className="invalid-feedback d-block">{errors.minScore}</div>}
      </div>

      <button type="submit" className="btn btn-primary" disabled={isLoading}>
        {isLoading ? (
          <>
            <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>⏳
            Chargement...
          </>
        ) : (
          "Rechercher"
        )}
      </button>
    </form>
  );
}
export default ClanSearchForm;

import React, { useState } from "react";
function ClanSearchForm() {
	const [formData, setFormData] = useState({
		nomClan: "", // Valeur initiale vide
		minMembers: "",
		maxMembers: "",
		minScore: "",
	});
	const handleChange = (e) => {
		const { name, value } = e.target;
		setFormData((prev) => ({
			...prev, // Garder les autres valeurs
			[name]: value, // Modifier seulement le champ changé
		}));
	};
	return (
		<form id="nom-clan-form" data-ajax-form="true" method="POST" action="">
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
				<div className="invalid-feedback">Veuillez entrer un nom</div>
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
				<div className="invalid-feedback">
					Le nombre minimum doit être au moins 2
				</div>
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
				<div className="invalid-feedback">
					Le nombre maximum doit être au plus 50
				</div>
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
				<div className="invalid-feedback">
					Le score minimum doit être positif ou nul
				</div>
			</div>

			<button type="submit" className="btn btn-primary">
				Rechercher
			</button>
		</form>
	);
}
export default ClanSearchForm;

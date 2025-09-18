//import './styles/app.scss';
import "./styles/app.css";
import ClanSearchForm from "./components/ClanSearchForm.jsx";
import Test from "./components/Test.jsx";
import React from "react";

let createRoot;
try {
	createRoot = require("react-dom/client").createRoot;
} catch (e) {
	console.warn("react-dom/client non disponible, fallback vers react-dom");
	const ReactDOM = require("react-dom");
	createRoot = (container) => ({
		render: (el) => ReactDOM.render(el, container),
	});
}

let rootTest = null;
let rootClanSearchForm = null;
function render() {
	const mountTest = document.getElementById("react-app");
	const mountClanSearchForm = document.getElementById("react-clan-search");
	if (mountTest) {
		if (!rootTest) {
			rootTest = createRoot(mountTest);
		}
		rootTest.render(<Test />);
	}
	if (mountClanSearchForm) {
		if (!rootClanSearchForm) {
			rootClanSearchForm = createRoot(mountClanSearchForm);
		}
		rootClanSearchForm.render(<ClanSearchForm />);
	}
}
render();
if (module.hot) {
	console.log("🔥 HMR available");
	module.hot.accept((err) => {
		if (err) console.error("HMR error", err);
		else render();
	});
	// 🎯 ACCEPTER TOUT LE DOSSIER COMPONENTS
	module.hot.accept(
		require.context("./components", true, /\.(js|jsx)$/),
		() => {
			console.log("🔄 HMR: Component modifié dans ./components/ !");
			render();
		}
	);
	module.hot.accept("./styles/app.css", () => console.log("🎨 CSS reloaded"));
}

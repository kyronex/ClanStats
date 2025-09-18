import React from "react";

function Test() {
	const [message] = React.useState("🎮 ClanStats React 18+ OPTIMISÉ ?? 🚀");
	const [counter, setCounter] = React.useState(0);
	const [lastUpdate, setLastUpdate] = React.useState(
		new Date().toLocaleTimeString()
	);
	React.useEffect(() => {
		console.log("🔄 mounted at", new Date().toLocaleTimeString());
	}, []);
	return (
		<div className="clanstats-app">
			<h1>{message}</h1>
			<div className="hot-reload-demo">
				<button
					onClick={() => setCounter((c) => c + 1)}
					className="btn btn-primary"
				>
					Compteur: {counter}
				</button>
				<button
					onClick={() => setLastUpdate(new Date().toLocaleTimeString())}
					className="btn btn-secondary"
				>
					🕒 Update: {lastUpdate}
				</button>
			</div>
			<div className="hot-reload-info">
				<p>React {React.version}</p>
				<small>ws://127.0.0.1:8081/ws</small>
			</div>
		</div>
	);
}
export default Test;

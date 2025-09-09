import './styles/app.css';
import React from 'react';

// ✅ CORRECTION: Import sécurisé de createRoot
let createRoot;
try {
    // Essai import moderne React 18+
    createRoot = require('react-dom/client').createRoot;
} catch (e) {
    console.warn('react-dom/client non disponible, fallback vers react-dom');
    // Fallback vers ReactDOM.render si createRoot indisponible
    const ReactDOM = require('react-dom');
    createRoot = (container) => ({
        render: (element) => ReactDOM.render(element, container)
    });
}

function ClanStatsApp() {
    console.log('PLOP - React 18+ createRoot');
    const [message, setMessage] = React.useState('🎮 ClanStats React 18+ OPTIMISÉ! 🚀');
    const [counter, setCounter] = React.useState(0);
    const [lastUpdate, setLastUpdate] = React.useState(new Date().toLocaleTimeString());

    React.useEffect(() => {
        console.log('🔄 ClanStatsApp React 18+ mounted/updated at:', new Date().toLocaleTimeString());
    }, []);

    return (
        <div className="clanstats-app">
            <h1>{message}</h1>

            <div className="hot-reload-demo">
                <button 
                    onClick={() => setCounter(c => c + 1)}
                    className="btn btn-primary me-2"
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
                <p>✨ <strong>React 18+ avec createRoot API</strong> - Plus d'erreurs modules!</p>
                <p>🔥 Hot reload avec conservation d'état</p>
                <p>📦 Version React: {React.version}</p>
                <small>WebSocket: ws://127.0.0.1:8081/ws • createRoot API</small>
            </div>
        </div>
    );
}

let root = null;

function render() {
    const reactMount = document.getElementById('react-app');
    if (reactMount) {
        if (!root) {
            root = createRoot(reactMount);
            console.log('🎯 React 18+ createRoot initialized');
        }
        root.render(<ClanStatsApp />);
        console.log('🎯 ClanStats React 18+ rendered at:', new Date().toLocaleTimeString());
    }
}

render();
console.log('🚀 ClanStats React 18+ initialized with createRoot API!');

if (module.hot) {
    console.log('🔥 Hot Module Replacement available with React 18+!');
    module.hot.accept((err) => {
        if (err) {
            console.error('❌ HMR Error:', err);
        } else {
            console.log('🔥 Hot reload activated - Re-rendering with createRoot!');
            render();
        }
    });

    module.hot.accept('./styles/app.css', () => {
        console.log('🎨 CSS Hot reloaded!');
    });
}

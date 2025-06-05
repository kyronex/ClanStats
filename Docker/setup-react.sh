#!/bin/bash
set -e

echo "🔧 Configuration React avec Webpack Encore..."

# Installation Webpack Encore
echo "📦 Installation Webpack Encore..."
composer require symfony/webpack-encore-bundle

# ✅ MODIFICATION: Configuration webpack.config.js SIMPLE pour proxy
echo "⚙️  Configuration Webpack avec Hot Reload..."
cat > webpack.config.js << 'EOF'
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    // ✅ Configuration Hot Reload SIMPLE
    .configureDevServerOptions(options => {
        options.host = '127.0.0.1';
        options.port = 8081;
        options.hot = true;
        options.liveReload = true;
        options.allowedHosts = 'all';
        
        // ✅ Configuration pour proxy Apache
        options.headers = {
            'Access-Control-Allow-Origin': 'http://localhost',
            'Access-Control-Allow-Credentials': true
        };
        
        // ✅ Public path pour proxy
        options.client = {
            webSocketURL: 'auto://0.0.0.0:0/sockjs-node'
        };
    })
    
    .enableReactPreset()
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
;

module.exports = Encore.getWebpackConfig();
EOF

# package.json simplifié
echo "📦 Configuration package.json..."
cat > package.json << 'EOF'
{
  "devDependencies": {
    "@symfony/webpack-encore": "^4.0.0",
    "@babel/core": "^7.17.0",
    "@babel/preset-env": "^7.16.0",
    "@babel/preset-react": "^7.16.0",
    "@babel/plugin-proposal-class-properties": "^7.16.0",
    "react": "^18.0.0",
    "react-dom": "^18.0.0",
    "webpack-notifier": "^1.15.0"
  },
  "license": "UNLICENSED",
  "private": true,
  "scripts": {
    "dev": "encore dev",
    "dev-server": "encore dev-server --hot",
    "watch": "encore dev --watch",
    "build": "encore production"
  }
}
EOF

# ✅ Création assets avec test hot reload simple
echo "📁 Création structure assets..."
mkdir -p assets/components
cat > assets/app.js << 'EOF'
import './styles/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';

// ✅ Composant simple pour tester le hot reload
function ClanStatsApp() {
    const [message, setMessage] = React.useState('🎮 ClanStats avec Hot Reload! 🔥');
    const [counter, setCounter] = React.useState(0);
    
    return (
        <div className="clanstats-app">
            <h1>{message}</h1>
            <p>Compteur de test: <strong>{counter}</strong></p>
            <button onClick={() => setCounter(counter + 1)}>
                ➕ Incrémenter
            </button>
            <button onClick={() => setCounter(0)}>
                🔄 Reset
            </button>
            <div className="hot-reload-info">
                <p>✨ <strong>Modifiez ce fichier</strong> et sauvegardez pour tester le hot reload!</p>
                <small>Port unique: http://localhost (Solution 1 active!)</small>
            </div>
        </div>
    );
}

// Montage du composant
const reactMount = document.getElementById('react-app');
if (reactMount) {
    const root = ReactDOM.createRoot(reactMount);
    root.render(<ClanStatsApp />);
}

console.log('🔄 ClanStats React ready with Hot Reload!');

// Hot module replacement
if (module.hot) {
    module.hot.accept('./app.js', () => {
        console.log('🔥 Hot reload activated!');
    });
}
EOF

mkdir -p assets/styles
cat > assets/styles/app.css << 'EOF'
/* ClanStats - Hot Reload Ready */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    margin: 0;
    padding: 20px;
}

.clanstats-app {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    text-align: center;
    backdrop-filter: blur(10px);
}

.clanstats-app h1 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 2rem;
}

button {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 24px;
    margin: 8px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
}

button:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.hot-reload-info {
    margin-top: 2rem;
    padding: 1rem;
    background: #e8f5e8;
    border-radius: 8px;
    border-left: 4px solid #27ae60;
}

.hot-reload-info p {
    margin: 0.5rem 0;
    color: #2c3e50;
}

.hot-reload-info small {
    color: #7f8c8d;
}
EOF

# Installation dépendances
echo "📦 Installation dépendances React..."
npm install

# Build initial
echo "🏗️  Build initial des assets..."
npm run dev

touch .react-configured
echo "✅ Configuration React avec Hot Reload terminée!"
#!/bin/bash
set -e

echo "🔧 Configuration React avec Webpack Encore..."

# Installation Webpack Encore
echo "📦 Installation Webpack Encore..."
composer require symfony/webpack-encore-bundle

# Configuration webpack.config.js
echo "⚙️  Configuration Webpack..."
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
    .enableReactPreset()
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
;

module.exports = Encore.getWebpackConfig();
EOF

# Création structure assets
echo "📁 Création structure assets..."
mkdir -p assets/components
cat > assets/app.js << 'EOF'
import './styles/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';

// Exemple composant React simple
function Welcome() {
    return <h1>🎉 React fonctionne avec ClanStats!</h1>;
}

// Montage conditionnel si élément existe
const reactMount = document.getElementById('react-app');
if (reactMount) {
    const root = ReactDOM.createRoot(reactMount);
    root.render(<Welcome />);
}

console.log('✅ React ready for ClanStats!');
EOF

mkdir -p assets/styles
cat > assets/styles/app.css << 'EOF'
/* ClanStats React styles */
body {
    background-color: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
}

#react-app {
    margin: 2rem;
    padding: 1rem;
    border: 2px solid #007bff;
    border-radius: 8px;
    background: white;
    text-align: center;
}
EOF

# Installation dépendances React
echo "📦 Installation dépendances React..."
npm install --save-dev react react-dom @babel/preset-react @babel/plugin-proposal-class-properties

# Build initial
echo "🏗️  Build initial des assets..."
npm run dev

# Marquer comme configuré
touch .react-configured
echo "✅ Configuration React terminée!"
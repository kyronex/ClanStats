#!/bin/bash
set -e

echo "🔧 Configuration React avec Webpack Encore optimisé..."

# ✅ CORRECTION: Forcer l'installation exacte React 18.2.0
echo "📦 Installation React 18+ et dépendances..."
npm install --save-dev @symfony/webpack-encore webpack-notifier @babel/core @babel/preset-env @babel/preset-react babel-loader @babel/plugin-proposal-class-properties @babel/plugin-transform-modules-commonjs

# ✅ CORRECTION: Installation exacte avec --exact pour éviter les conflits
npm install react@18.2.0 react-dom@18.2.0 --save --exact

# ✅ CORRECTION: Vérification immédiate que react-dom/client existe
echo "🔍 Vérification react-dom/client disponible..."
if [ ! -f "node_modules/react-dom/client.js" ]; then
    echo "❌ react-dom/client manquant - Réinstallation forcée..."
    rm -rf node_modules/react node_modules/react-dom
    npm install react@18.2.0 react-dom@18.2.0 --save --exact --force
fi

# Création de la structure
echo "📁 Création de la structure des assets..."
mkdir -p assets/styles assets/components

# ✅ Configuration webpack.config.js compatible
echo "⚙️ Configuration webpack.config.js compatible..."
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
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        // ✅ PLUGINS BABEL MODERNES
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3';
        // ✅ TARGETS MODERNES
        config.targets = {
            browsers: ['> 1%', 'last 2 versions', 'not dead']
        };
    })
    .enableReactPreset()
    .enableSassLoader()
    
    // 🎯 CONFIGURATION DEV-SERVER CORRIGÉE
    .configureDevServerOptions(options => {
        // Configuration serveur de base
        options.server = {
            type: 'http'  // ✅ SYNTAXE CORRECTE
        };
        options.port = 8081;
        options.host = '0.0.0.0';
        options.allowedHosts = 'all';

        // ✅ HEADERS CORS PRINCIPALES
        options.headers = {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'X-Requested-With, Content-Type, Authorization'
        };

        // ✅ HOT RELOAD SIMPLIFIÉ
        options.hot = true;
        options.liveReload = true;

        // ✅ WATCH FILES OPTIMISÉ
        options.watchFiles = {
            paths: ['src/**/*.php', 'templates/**/*.twig', 'assets/**/*'],
            options: {
                usePolling: true,
                interval: 1000
            }
        };

        // ✅ CLIENT CONFIGURATION SIMPLIFIÉE
        options.client = {
            overlay: {
                errors: true,
                warnings: false
            },
            progress: true,
            reconnect: 5,
            // ✅ WebSocket via Apache proxy
            webSocketURL: 'ws://localhost/ws'
        };

        // Autres options utiles
        options.compress = true;
        options.historyApiFallback = true;
        
        // ✅ DEV MIDDLEWARE OPTIMISÉ
        options.devMiddleware = {
            writeToDisk: false, // ✅ FALSE pour de meilleures performances
            publicPath: '/build/'
        };
    });

// 🎯 CONFIGURATION DEV vs PROD
if (Encore.isDev()) {
    // ✅ PUBLIC PATH POUR LE DÉVELOPPEMENT
    Encore.setPublicPath('http://localhost:8081/build');
    Encore.setManifestKeyPrefix('build/');
}

// Configuration webpack avancée
const webpackConfig = Encore.getWebpackConfig();

// ✅ ALIAS REACT OPTIMISÉS
webpackConfig.resolve = {
    ...webpackConfig.resolve,
    alias: {
        'react': require.resolve('react'),
        'react-dom': require.resolve('react-dom'),
        'react-dom/client': require.resolve('react-dom/client')
    },
    // ✅ FALLBACKS POUR ENVIRONNEMENT BROWSER
    fallback: {
        "crypto": false,
        "stream": false,
        "buffer": false,
        "path": false,
        "fs": false,
        "os": false,
        "util": false
    }
};

// ✅ OPTIMISATIONS DÉVELOPPEMENT
if (!Encore.isProduction()) {
    webpackConfig.mode = 'development';
    webpackConfig.devtool = 'eval-cheap-module-source-map'; // ✅ PLUS RAPIDE
    
    webpackConfig.optimization = {
        ...webpackConfig.optimization,
        minimize: false,
        sideEffects: false,
        removeAvailableModules: false,
        removeEmptyChunks: false,
        splitChunks: false
    };
    
    // ✅ PERFORMANCE OPTIMIZATIONS
    webpackConfig.resolve.symlinks = false;
    webpackConfig.cache = {
        type: 'memory'
    };
}

// ✅ IGNORER WARNINGS NON CRITIQUES
webpackConfig.ignoreWarnings = [
    /Module not found: Error: Can't resolve/,
    /source-map-loader/,
    /Critical dependency/,
    /the request of a dependency is an expression/
];

// ✅ STATS CONFIGURATION POUR MOINS DE VERBOSITÉ
webpackConfig.stats = 'minimal';

module.exports = webpackConfig;
EOF

# ✅ Package.json avec React 18+ strict
echo "📋 Configuration package.json avec React 18+..."
cat > package.json << 'EOF'
{
    "devDependencies": {
        "@babel/core": "^7.17.0",
        "@babel/plugin-proposal-class-properties": "^7.16.0",
        "@babel/plugin-transform-modules-commonjs": "^7.16.0",
        "@babel/preset-env": "^7.16.0",
        "@babel/preset-react": "^7.16.0",
        "@symfony/webpack-encore": "^4.0.0",
        "babel-loader": "^8.0.0",
        "core-js": "^3.23.0",
        "css-loader": "^6.0.0",
        "file-loader": "^6.0.0",
        "sass-loader": "^13.0.0",
        "sass": "^1.0.0",
        "webpack-notifier": "^1.6.0"
    },
    "license": "UNLICENSED",
    "private": true,
    "scripts": {
        "dev-server": "encore dev-server",
        "dev": "encore dev",
        "watch": "encore dev --watch",
        "build": "encore production --progress"
    },
    "dependencies": {
        "react": "18.2.0",
        "react-dom": "18.2.0"
    }
}
EOF

# ✅ CORRECTION: Import explicite avec try/catch pour React 18+
echo "⚛️  Création assets/app.js avec React 18+ createRoot sécurisé..."
cat > assets/app.js << 'EOF'
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
EOF

# CSS identique
echo "🎨 Création assets/styles/app.css..."
cat > assets/styles/app.css << 'EOF'
.clanstats-app {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.clanstats-app h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hot-reload-demo {
    margin: 2rem 0;
}

.hot-reload-demo button {
    margin: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.hot-reload-demo .btn-primary {
    background: #28a745;
    color: white;
}

.hot-reload-demo .btn-secondary {
    background: #17a2b8;
    color: white;
}

.hot-reload-demo button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.hot-reload-info {
    background: rgba(255,255,255,0.1);
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 2rem;
    border: 2px solid rgba(255,255,255,0.2);
}

.hot-reload-info p {
    margin: 0.5rem 0;
    font-size: 1.1rem;
}

.hot-reload-info small {
    color: rgba(255,255,255,0.8);
    font-family: monospace;
    background: rgba(0,0,0,0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}
EOF

# ✅ CORRECTION: Installation avec nettoyage préventif
echo "📦 Installation des dépendances React 18+ optimisées..."
rm -rf node_modules package-lock.json 2>/dev/null || true
npm install

# ✅ CORRECTION: Test final que react-dom/client est bien accessible
echo "🔍 Test final react-dom/client..."
node -e "
try { 
    const { createRoot } = require('react-dom/client'); 
    console.log('✅ react-dom/client OK - createRoot:', typeof createRoot);
} catch(e) { 
    console.log('❌ react-dom/client KO:', e.message);
    console.log('📦 Réinstallation react-dom...');
    process.exit(1);
}
" || {
    echo "🔧 Réinstallation forcée react-dom..."
    npm uninstall react-dom
    npm install react-dom@18.2.0 --save --exact
}

echo "🧹 Nettoyage cache avant build..."
npm cache clean --force
rm -rf node_modules/.cache

echo "🏗️  Build initial React 18+ optimisé..."
npm run dev

echo "🔍 Vérification finale..."
if [ -f "public/build/app.js" ]; then
    echo "✅ Build React 18+ OPTIMISÉ réussi!"
    ls -la public/build/ | grep -E '\.(js|css)$' || true
else
    echo "⚠️  Tentative build production..."
    npm run build
fi

touch .react-configured
echo "✅ Configuration React 18+ OPTIMISÉE terminée!"
echo "🚀 Prêt avec createRoot API - zéro erreur react-dom/client!"

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

#!/bin/bash
set -e

echo "🔧 Configuration React avec Webpack Encore optimisé..."

echo "📦 Installation dépendances dev..."
npm install --save-dev @symfony/webpack-encore webpack-notifier @babel/core @babel/preset-env @babel/preset-react babel-loader @babel/plugin-proposal-class-properties @babel/plugin-transform-modules-commonjs

echo "📦 Installation React 18 exacte..."
npm install react@18.2.0 react-dom@18.2.0 --save --exact

echo "🔍 Vérification react-dom/client..."
if [ ! -f "node_modules/react-dom/client.js" ]; then
  echo "❌ react-dom/client manquant - réinstallation..."
  rm -rf node_modules/react node_modules/react-dom
  npm install react@18.2.0 react-dom@18.2.0 --save --exact --force
fi

echo "📁 Création structure assets..."
mkdir -p assets/styles assets/components

echo "⚙️ Écriture webpack.config.js..."
cat > webpack.config.js <<'EOF'
const Encore = require('@symfony/webpack-encore');
if (!Encore.isRuntimeEnvironmentConfigured()) Encore.configureRuntimeEnvironment(process.env.NODE_ENV||'dev');
Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app','./assets/app.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(config => { config.plugins.push('@babel/plugin-proposal-class-properties'); })
  .configureBabelPresetEnv(config => { config.useBuiltIns='usage'; config.corejs='3.23'; config.targets={browsers:['> 1%','last 2 versions','not dead']}; })
  .enableReactPreset()
  .enableSassLoader()
  .configureDevServerOptions(options => {
    options.server = { type: 'http' };
    options.port = 8081;
    options.host = '0.0.0.0';
    options.allowedHosts = 'all';
    options.headers = {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'X-Requested-With, Content-Type, Authorization'
    };
    options.hot = true;
    options.liveReload = true;
    options.watchFiles = { paths: ['src/**/*.php','templates/**/*.twig','assets/**/*'], options: { usePolling:true, interval:1000 } };
    options.client = { overlay:{errors:true,warnings:false}, progress:true, reconnect:5, webSocketURL:'ws://localhost/ws' };
    options.compress = true;
    options.historyApiFallback = true;
    options.devMiddleware = { writeToDisk:false, publicPath:'/build/' };
  });
if (Encore.isDev()) { Encore.setPublicPath('http://localhost:8081/build'); Encore.setManifestKeyPrefix('build/'); }
const webpackConfig = Encore.getWebpackConfig();
webpackConfig.resolve = { ...webpackConfig.resolve, alias: { 'react': require.resolve('react'), 'react-dom': require.resolve('react-dom'), 'react-dom/client': require.resolve('react-dom/client') }, fallback: { crypto:false,stream:false,buffer:false,path:false,fs:false,os:false,util:false } };
if (!Encore.isProduction()) {
  webpackConfig.mode = 'development';
  webpackConfig.devtool = 'eval-cheap-module-source-map';
  webpackConfig.optimization = { ...webpackConfig.optimization, minimize:false, sideEffects:false, removeAvailableModules:false, removeEmptyChunks:false, splitChunks:false };
  webpackConfig.resolve.symlinks = false;
  webpackConfig.cache = { type:'memory' };
}
webpackConfig.ignoreWarnings = [/Module not found: Error: Can't resolve/,/source-map-loader/,/Critical dependency/,/the request of a dependency is an expression/];
webpackConfig.stats = 'minimal';
module.exports = webpackConfig;
EOF

echo "📋 Écriture package.json..."
cat > package.json <<'EOF'
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

echo "⚛️ Écriture assets/app.js..."
cat > assets/app.js <<'EOF'
import './styles/app.css';
import React from 'react';
let createRoot;
try { createRoot = require('react-dom/client').createRoot; }
catch(e){
  console.warn('react-dom/client non disponible, fallback vers react-dom');
  const ReactDOM = require('react-dom');
  createRoot = (container) => ({ render: (el) => ReactDOM.render(el, container) });
}
function ClanStatsApp(){
  const [message] = React.useState('🎮 ClanStats React 18+ OPTIMISÉ! 🚀');
  const [counter,setCounter] = React.useState(0);
  const [lastUpdate,setLastUpdate] = React.useState(new Date().toLocaleTimeString());
  React.useEffect(()=>{ console.log('🔄 mounted at', new Date().toLocaleTimeString()); },[]);
  return (
    <div className="clanstats-app">
      <h1>{message}</h1>
      <div className="hot-reload-demo">
        <button onClick={()=>setCounter(c=>c+1)} className="btn btn-primary">Compteur: {counter}</button>
        <button onClick={()=>setLastUpdate(new Date().toLocaleTimeString())} className="btn btn-secondary">🕒 Update: {lastUpdate}</button>
      </div>
      <div className="hot-reload-info">
        <p>React {React.version}</p>
        <small>ws://127.0.0.1:8081/ws</small>
      </div>
    </div>
  );
}
let root=null;
function render(){
  const mount = document.getElementById('react-app');
  if(!mount) return;
  if(!root){ root = createRoot(mount); console.log('🎯 createRoot initialized'); }
  root.render(<ClanStatsApp/>);
  console.log('🎯 rendered at', new Date().toLocaleTimeString());
}
render();
if(module.hot){ console.log('🔥 HMR available'); module.hot.accept(err=>{ if(err) console.error('HMR error',err); else render(); }); module.hot.accept('./styles/app.css',()=>console.log('🎨 CSS reloaded')); }
EOF

echo "🎨 Écriture assets/styles/app.css..."
cat > assets/styles/app.css <<'EOF'
.clanstats-app{max-width:800px;margin:2rem auto;padding:2rem;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px;color:#fff;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,.3)}
.clanstats-app h1{font-size:2rem;margin-bottom:1rem}
.hot-reload-demo{margin:1.5rem 0}
.hot-reload-demo button{margin:.4rem;padding:.6rem 1rem;border:none;border-radius:8px;cursor:pointer}
.hot-reload-demo .btn-primary{background:#28a745;color:#fff}
.hot-reload-demo .btn-secondary{background:#17a2b8;color:#fff}
.hot-reload-info{background:rgba(255,255,255,.08);padding:1rem;border-radius:8px;margin-top:1rem}
EOF

echo "📦 Nettoyage avant install..."
rm -rf node_modules package-lock.json 2>/dev/null || true

echo "📥 Installation npm..."
npm install

echo "🔍 Test final react-dom/client..."
node -e "try{ const { createRoot } = require('react-dom/client'); console.log('✅ react-dom/client OK', typeof createRoot); }catch(e){ console.error('❌ react-dom/client KO', e.message); process.exit(1); }" || {
  echo "🔧 Réinstallation react-dom..."
  npm uninstall react-dom
  npm install react-dom@18.2.0 --save --exact
}

echo "🧹 Nettoyage cache..."
npm cache clean --force >/dev/null 2>&1 || true
rm -rf node_modules/.cache 2>/dev/null || true

echo "🏗️ Build dev (npm run dev)..."
npm run dev || { echo "⚠️ Build dev failed, trying production build..."; npm run build || true; }

echo "🔍 Vérification finale..."
if [ -f "public/build/app.js" ]; then
  echo "✅ Build réussi"
  ls -la public/build/ | grep -E '\.(js|css)$' || true
else
  echo "⚠️ Build non trouvé, lancer 'npm run build' manuellement"
fi

touch .react-configured
echo "✅ Configuration terminée"

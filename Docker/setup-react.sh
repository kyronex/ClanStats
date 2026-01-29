#!/bin/bash
set -e

echo "🔧 Configuration React + TypeScript avec Webpack Encore..."

# -------------------------------------------------
# 📦 Dépendances DEV
# -------------------------------------------------
echo "📦 Installation dépendances dev..."
npm install --save-dev \
  @symfony/webpack-encore \
  webpack-notifier \
  @babel/core \
  @babel/preset-env \
  @babel/preset-react \
  @babel/preset-typescript \
  babel-loader \
  @babel/plugin-proposal-class-properties \
  @babel/plugin-transform-modules-commonjs \
  typescript \
  @types/react \
  @types/react-dom

echo "📦 Installation React 18 exacte..."
npm install react@18.2.0 react-dom@18.2.0 --save --exact

echo "📦 Installation Chart.js et react-chartjs-2..."
npm install chart.js react-chartjs-2 --save

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
const Encore = require("@symfony/webpack-encore");
const path = require("path");
if (!Encore.isRuntimeEnvironmentConfigured())
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
Encore.setOutputPath("public/build/")
	.setPublicPath("/build")
	.addEntry("app", "./assets/app.tsx")
	.splitEntryChunks()
	.enableSingleRuntimeChunk()
	.cleanupOutputBeforeBuild()
	.enableBuildNotifications()
	.enableSourceMaps(!Encore.isProduction())
	.enableVersioning(Encore.isProduction())
  .enableBabelTypeScriptPreset()
	.configureBabel((config) => {
    config.presets = [
      [
        "@babel/preset-env",
        {
          useBuiltIns: "usage",
          corejs: "3.23",
          targets: {
            browsers: ["> 1%", "last 2 versions", "not dead"],
          },
        },
      ],
      [
        "@babel/preset-react",
        {
          runtime: "automatic", // ← CRUCIAL
        },
      ],
			"@babel/preset-typescript"
    ];
    config.plugins = ["@babel/plugin-proposal-class-properties"];
  })
	.enableSassLoader()
	.configureDevServerOptions((options) => {
		options.port = 8081;
		options.host = "0.0.0.0";
		options.allowedHosts = "all";
		options.hot = true;
		options.liveReload = false;

		// ✅ FIX SYNTAXE WATCHFILES
		options.watchFiles = [
			{
				paths: ["./assets/**/*"],
				options: { usePolling: true, interval: 1000, ignored: /node_modules/ },
			},
		];

		options.devMiddleware = {
			writeToDisk: true,
			publicPath: "/build/",
		};
	});

if (Encore.isDev()) {
	Encore.setPublicPath("http://localhost:8081/build");
	Encore.setManifestKeyPrefix("build/");
}

const webpackConfig = Encore.getWebpackConfig();

// ✅ AJOUT CRUCIAL : WATCH OPTIONS WEBPACK
webpackConfig.watchOptions = {
	poll: 1000,
	aggregateTimeout: 300,
	ignored: /node_modules/,
};

webpackConfig.resolve = {
	...webpackConfig.resolve,
	alias: {
		"react/jsx-runtime": path.resolve(__dirname, "node_modules/react/jsx-runtime.js"),
    "react/jsx-dev-runtime": path.resolve(__dirname, "node_modules/react/jsx-dev-runtime.js"),
	},
	fallback: {
		crypto: false,
		stream: false,
		buffer: false,
		path: false,
		fs: false,
		os: false,
		util: false,
	},
};

if (!Encore.isProduction()) {
	webpackConfig.mode = "development";
	webpackConfig.devtool = "eval-cheap-module-source-map";
	webpackConfig.optimization = {
		...webpackConfig.optimization,
		minimize: false,
		sideEffects: false,
		removeAvailableModules: false,
		removeEmptyChunks: false,
		splitChunks: false,
	};
	webpackConfig.resolve.symlinks = false;
	webpackConfig.cache = { type: "memory" };
}

webpackConfig.ignoreWarnings = [
	/Module not found: Error: Can't resolve/,
	/source-map-loader/,
	/Critical dependency/,
	/the request of a dependency is an expression/,
];
webpackConfig.stats = "minimal";
module.exports = webpackConfig;
EOF

echo "📋 Écriture package.json..."
cat > package.json <<'EOF'
{
  "devDependencies": {
    "@babel/core": "^7.17.0",
    "@babel/plugin-proposal-class-properties": "^7.16.0",
    "@babel/plugin-transform-modules-commonjs": "^7.27.1",
    "@babel/preset-env": "^7.16.0",
    "@babel/preset-react": "^7.16.0",
    "@symfony/webpack-encore": "^4.0.0",
    "babel-loader": "^8.0.0",
    "core-js": "^3.23.0",
    "css-loader": "^6.0.0",
    "file-loader": "^6.0.0",
    "sass": "^1.0.0",
    "sass-loader": "^13.0.0",
    "webpack-notifier": "^1.6.0",
		"typescript": "^5.3.0"
  },
  "private": true,
  "scripts": {
    "dev-server": "encore dev-server",
    "dev": "encore dev",
    "watch": "encore dev --watch",
    "build": "encore production --progress",
		"typecheck": "tsc --noEmit"
  },
  "dependencies": {
    "react": "18.2.0",
    "react-dom": "18.2.0",
		"chart.js": "^4.4.0",
    "react-chartjs-2": "^5.2.0"
  }
}
EOF

echo "📄 Écriture tsconfig.json..."
cat > tsconfig.json <<'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["DOM", "ES2020"],
    "module": "ESNext",
    "moduleResolution": "Node",
    "jsx": "react-jsx",

    "strict": false,
    "noImplicitAny": true,
    "checkJs": false,

    "allowJs": true,
    "isolatedModules": true,
    "esModuleInterop": true,
    "skipLibCheck": true,

    "forceConsistentCasingInFileNames": true,
    "noEmit": true
  },
  "include": ["assets/**/*"],
  "exclude": ["node_modules", "vendor"]
}

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

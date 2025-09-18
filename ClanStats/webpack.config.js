const Encore = require("@symfony/webpack-encore");
if (!Encore.isRuntimeEnvironmentConfigured())
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
Encore.setOutputPath("public/build/")
	.setPublicPath("/build")
	.addEntry("app", "./assets/app.js")
	.splitEntryChunks()
	.enableSingleRuntimeChunk()
	.cleanupOutputBeforeBuild()
	.enableBuildNotifications()
	.enableSourceMaps(!Encore.isProduction())
	.enableVersioning(Encore.isProduction())
	.configureBabel((config) => {
		config.plugins.push("@babel/plugin-proposal-class-properties");
	})
	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = "usage";
		config.corejs = "3.23";
		config.targets = { browsers: ["> 1%", "last 2 versions", "not dead"] };
	})
	.enableReactPreset()
	.enableSassLoader()
	.configureDevServerOptions((options) => {
		options.port = 8081;
		options.host = "0.0.0.0";
		options.allowedHosts = "all";
		options.hot = false;
		options.liveReload = true;

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
		react: require.resolve("react"),
		"react-dom": require.resolve("react-dom"),
		"react-dom/client": require.resolve("react-dom/client"),
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

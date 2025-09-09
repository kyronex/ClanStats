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

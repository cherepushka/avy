const Encore = require('@symfony/webpack-encore');
const dotenv = require('dotenv')
const dotenvExpand = require('dotenv-expand')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build')

    .copyFiles({
        from: './resources/assets/img',
        to: '../img/[path][name].[ext]',
        pattern: /\.(png|jpg|jpeg|svg)$/
    })
    .copyFiles({
        from: './resources/assets/css',
        to: '../css/[path][name].[ext]',
    })
    .copyFiles({
        from: './resources/assets/fonts',
        to: '../fonts/[path][name].[ext]',
    })
    .copyFiles({
        from: './resources/js/vendor',
        to: '../js/[path][name].[ext]',
    })

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './resources/js/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()

    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // .configureBabel((config) => {
    //     config.plugins.push('@babel/plugin-proposal-class-properties');
    // })

    .configureDefinePlugin(options => {
        const env = dotenv.config();
        dotenvExpand.expand(env)

        if (env.error) {
            throw env.error;
        }

        options['process.env.APP_MAIN_DOMAIN'] = '\"' + env.parsed.APP_MAIN_DOMAIN + '\"';
        options['process.env.APP_MAIN_URL'] = '\"' + env.parsed.APP_MAIN_URL + '\"';
    })

    // enables @babel/preset-env polyfills
    // .configureBabelPresetEnv((config) => {
    //     config.useBuiltIns = 'usage';
    //     config.corejs = 3;
    // })

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    // .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    .copyFiles({
        from: 'assets/img',
    
       // optional target path, relative to the output dir
        to: 'images/[name].[hash:8].[ext]',
        
        // if versioning is enabled, add the file hash too
        //to: 'images/[path][name].[hash:8].[ext]',
        // only copy files matching this pattern
        pattern: /\.(png|jpg|jpeg)$/
    })

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('back_app', './assets/js/back_app.js')
    .addEntry('front_app', './assets/js/front_app.js')
    .addEntry('situ_create_app', './assets/js/situ_create_app.js')
    .addEntry('situ_list_app', './assets/js/situ_list_app.js')
    .addEntry('user_account_app', './assets/js/user_account_app.js')
    .addEntry('user_update_app', './assets/js/user_update_app.js')
    
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
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();

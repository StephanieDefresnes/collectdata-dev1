var Encore = require('@symfony/webpack-encore');

// Define App configuration
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .copyFiles({
        from: 'assets/img',
        to: 'img/[name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg)$/
    })
    .addEntry('app', './assets/js/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .autoProvidejQuery()
;

// Build the first configuration
const appConfig = Encore.getWebpackConfig();

// Set a unique name for the config (needed later!)
appConfig.name = 'appConfig';

/*
 * Define FrontOffice configuration
 */
// reset Encore to build frontApp
Encore.reset();

Encore
    .setOutputPath('public/build/front/')
    .setPublicPath('/build/front')
    .copyFiles({
        from: 'assets/front/img',
        to: 'build/front/img/[name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg)$/
    })
    .addEntry('front_app', './assets/front/js/front_app.js')
    .addEntry('situ_create_app', './assets/front/js/situ_create_app.js')
    .addEntry('situ_list_app', './assets/front/js/situ_list_app.js')
    .addEntry('user_account_app', './assets/front/js/user_account_app.js')
    .addEntry('user_update_app', './assets/front/js/user_update_app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .autoProvidejQuery()
;

// Build the first configuration
const frontApp = Encore.getWebpackConfig();

// Set a unique name for the config (needed later!)
frontApp.name = 'frontApp';

/*
 * Define BackOffice configuration
 */
// reset Encore to build backApp
Encore.reset();

Encore
    .setOutputPath('public/build/back/')
    .setPublicPath('/build/back')
    .copyFiles({
        from: 'assets/back/img',
        to: 'build/back/img/[name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg)$/
    })
    .addEntry('back_app', './assets/back/js/back_app.js')
    .addEntry('index_app', './assets/back/js/index_app.js')
    .addEntry('lang_search_app', './assets/back/js/lang_search_app.js')
    .addEntry('user_search_app', './assets/back/js/user_search_app.js')
    .addEntry('user_can_delete', './assets/back/js/user_can_delete.js')
    .addEntry('user_can_multi_select', './assets/back/js/user_can_multi_select.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .autoProvidejQuery()
;

// Build the first configuration
const backApp = Encore.getWebpackConfig();

// Set a unique name for the config (needed later!)
backApp.name = 'backApp';

// export the final configuration as an array of multiple configurations
module.exports = [appConfig, frontApp, backApp];

var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .autoProvideVariables({
        "window.Bloodhound": require.resolve('bloodhound-js'),
        "jQuery.tagsinput": "bootstrap-tagsinput"
    })
    .enableSassLoader()
    .enableVersioning()
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/login', './assets/js/login.js')
    .addEntry('js/admin', './assets/js/admin.js')
    .addEntry('js/search', './assets/js/search.js')
    .addEntry('profile_album_js', './assets/js/blueimp.js')
    .addStyleEntry('css/album_css', ['./assets/scss/blueimp.scss'])
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
    .addStyleEntry('css/admin', ['./assets/scss/admin.scss'])
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

;

module.exports = Encore.getWebpackConfig();

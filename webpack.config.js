var Encore = require('@symfony/webpack-encore');
var path = require('path');

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
    .addEntry('js/searchSingles', './assets/js/searchSingles.js')
    //.addEntry('js/jquery-file-upload',  path.resolve(__dirname, 'node_modules/blueimp-file-upload/js/jquery.fileupload.js') )
    .addEntry('js/album', './assets/js/album.js')
    .addEntry('blueimp_demo', './assets/js/blueimpdemo.js')
    .addStyleEntry('css/album_css', ['./assets/scss/album.scss'])
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
    .addStyleEntry('css/custom', ['./assets/scss/custom.scss'])
    .addStyleEntry('css/admin', ['./assets/scss/admin.scss'])
    //.splitEntryChunks()
    .disableSingleRuntimeChunk()

;
var config = Encore.getWebpackConfig();
config.resolve.alias = {
    'blueimp-jquery-file-upload': path.resolve(__dirname, 'node_modules/blueimp-file-upload/js/jquery.fileupload.js'),
};

module.exports = config;

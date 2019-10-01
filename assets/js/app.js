// loads the Bootstrap jQuery plugins
// Popper included in .bundle.js
require('bootstrap/dist/js/bootstrap.bundle.js');

import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

import 'bootstrap-sass/assets/javascripts/bootstrap/transition.js';
import 'bootstrap-sass/assets/javascripts/bootstrap/alert.js';
import 'bootstrap-sass/assets/javascripts/bootstrap/collapse.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap/dropdown.js';
import 'bootstrap-sass/assets/javascripts/bootstrap/modal.js';
import 'jquery'

// loads the code syntax highlighting library
import './highlight.js';

// Creates links to the Symfony documentation
import './doclinks.js';

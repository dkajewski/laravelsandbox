import './bootstrap';
import Vue from 'vue';
import VueInternationalization from 'vue-i18n';
import Locale from '../assets/js/vue-i18n-locales.generated';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueCookies from 'vue-cookies';
import Vuex from 'vuex';
import 'es6-promise/auto';
import VueSidebarMenu from 'vue-sidebar-menu';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

import Routes from './routes.js';
import App from './views/App';

Vue.use(VueInternationalization, VueAxios, axios, VueCookies, Loading);
Vue.use(Vuex);
Vue.use(VueSidebarMenu);
const lang = localStorage.getItem('lang') ? localStorage.getItem('lang') : document.documentElement.lang.substr(0, 2);
const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale,
    silentTranslationWarn: true,
});

/**
 * Registering components
 */
Vue.component('admin-sidebar', require('./components/admin/menus/Sidebar').default);
Vue.component('game-sidebar', require('./components/menus/GameSidebar.vue').default);
Vue.component('login', require('./components/basic/Login.vue').default);
Vue.component('navbar', require('./components/menus/Navbar.vue').default);
Vue.component('register', require('./components/basic/Register.vue').default);
Vue.component('font-awesome-icon', FontAwesomeIcon);
Vue.component('loading', Loading);
Vue.config.productionTip = false;

const store = new Vuex.Store({
    state: {
        token: localStorage.getItem('accessToken') !== null
           && localStorage.getItem('accessToken') !== 'undefined'
           && localStorage.getItem('accessToken').length > 0,
        loaderCount: 0,
        userId: localStorage.getItem('userId'),
    },

    getters: {

    },

    mutations: {
        updateToken: function (state, newValue) {
            state.token = newValue;
        },
        updateUserId: function (state, newValue) {
            state.userId = newValue;
        },
        updateLoaderCount: function (state, newValue) {
            state.loaderCount += newValue;
        }
    }
});

const app = new Vue({
    el: '#app',
    i18n,
    router: Routes,
    store,
    components: {Loading},
    render: h => h(App),
});

export default app;
<!--@todo: prepare mobile menu with categories from game sidebar -->
<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <router-link tag="a" class="navbar-brand" to="/">Navbar</router-link>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto d-none d-lg-flex">
                <router-link tag="li" class="nav-item nav-link" to="/messages">
                    <font-awesome-icon :icon="['fas', 'comments']"></font-awesome-icon>
                </router-link>
            </ul>
            <div class="dropdown show" v-show="token && window.width >= 992">
                <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <font-awesome-icon :icon="['fas', 'user-circle']"></font-awesome-icon>
                </a>
                <div class="dropdown-menu dropdown-menu-right" v-show="token && window.width >= 992" aria-labelledby="dropdownMenuLink">
                    <router-link tag="a" class="dropdown-item" :to="{path: '/user/'+userId}">{{ $t('basic.user-profile') }}</router-link>
                    <router-link tag="a" class="dropdown-item" :to="{path: '/settings'}">{{ $t('basic.settings') }}</router-link>
                    <a class="dropdown-item" href="#" v-on:click="logout">{{ $t('basic.logout') }}</a>
                </div>
            </div>
            <router-link v-show="!token && window.width >= 992" tag="button" class="btn btn-outline-info" to="/login">{{ $t('basic.login') }}</router-link>
            <ul class="navbar-nav mr-auto d-lg-none">
                <router-link v-show="token" tag="li" class="nav-item nav-link" to="/messages" data-toggle="collapse" data-target="#navbarNav">{{$t('basic.messages')}}</router-link>
                <router-link v-show="!token" tag="li" class="nav-item nav-link" to="/login" data-toggle="collapse" data-target="#navbarNav">{{ $t('basic.login') }}</router-link>
                <router-link tag="li" v-show="token" class="nav-item nav-link" :to="{path: '/user/'+userId}" data-toggle="collapse" data-target="#navbarNav">{{ $t('basic.user-profile') }}</router-link>
                <router-link tag="li" v-show="token" class="nav-item nav-link" :to="{path: '/settings'}" data-toggle="collapse" data-target="#navbarNav">{{ $t('basic.settings') }}</router-link>
                <a class="nav-item nav-link" v-show="token" href="#" v-on:click="logout" data-toggle="collapse" data-target="#navbarNav">{{ $t('basic.logout') }}</a>
            </ul>
        </div>
    </nav>
</template>
<script>
    export default {
        data () {
            return {
                window: {
                    width: 0,
                    height: 0,
                },
            };
        },

        computed: {
            token () {
                return this.$store.state.token;
            },

            userId () {
                return this.$store.state.userId;
            }
        },

        mounted () {
            window.addEventListener('resize', this.handleResize);
            this.handleResize();
        },

        methods: {
            logout: function () {
                this.$store.commit('updateLoaderCount', 1);
                let self = this;
                let headers = {
                    headers: {
                        'Authorization': 'Bearer '+localStorage.getItem('accessToken')
                    }
                };
                axios.get('/api/auth/logout', headers)
                    .then(function () {
                        localStorage.accessToken = '';
                        self.$store.commit('updateToken', false);
                        localStorage.userId = null;
                        self.$store.commit('updateUserId', null);
                        self.$store.commit('updateLoaderCount', -1);
                        location.replace('/');
                    });
            },
            handleResize: function () {
                this.window.width = document.documentElement.clientWidth;
                this.window.height = document.documentElement.clientHeight;
            },
        },

        beforeDestroy () {
            window.removeEventListener('resize', this.handleResize);
        }
    }
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUserCircle, faComments} from '@fortawesome/free-solid-svg-icons'
    library.add(faUserCircle, faComments);
</script>
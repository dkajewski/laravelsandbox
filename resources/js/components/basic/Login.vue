<template>
    <div class="container">
        <div class="menu-placeholder"></div>
        <div class="col-md-12">
            <h1>{{ $t('basic.login') }}</h1>
            <div class="card">
                <div class="card-body">
                    <div class="error" v-show="error">
                        <p v-for="(message, index) in messages">
                            {{ $t(message) + (messagesData[index] === undefined ? '' : messagesData[index])}}
                        </p>
                    </div>
                    <div class="success" v-show="success || token">
                        <p>{{ $t('basic.login-success') }}</p>
                    </div>
                    <div v-show="!token">
                        <form @submit="loginUser">
                            <div class="form-group">
                                <input type="text" class="form-control" id="login" name="login" v-model="login" :placeholder="$t('basic.email')"/>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="password" name="password" v-model="password" :placeholder="$t('basic.password')"/>
                            </div>
                            <div class="form-group">
                                <router-link tag="a" to="/register">{{ $t('basic.register-info') }}</router-link>
                            </div>
                            <div class="form-group d-flex justify-content-center">
                                <button class="btn btn-primary">
                                    {{ $t('basic.login-perform') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                login: '',
                password: '',
                error: false,
                success: false,
                messages: [],
                messagesData: [],
            }
        },

        computed: {
            token () {
                return this.$store.state.token;
            }
        },

        methods: {
            loginUser: function (e) {
                this.$store.commit('updateLoaderCount', 1);
                e.preventDefault();
                let formData = {
                    email: this.login,
                    password: this.password
                };

                axios.post('/api/auth/login', formData)
                    .then((response) => {
                        if (response.data.success) {
                            localStorage.accessToken = response.data['access_token'];
                            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('accessToken');
                            this.$store.commit('updateToken', true);
                            localStorage.userId = response.data['user_id'];
                            this.$store.commit('updateUserId', localStorage.userId);
                            this.reloadEchoService();
                            this.error = false;
                            this.success = true;
                        } else {
                            localStorage.accessToken = '';
                            this.$store.commit('updateToken', false);
                            this.error = true;
                            this.messages = [];
                            for (let i in response.data.original.errors) {
                                this.messages.push(response.data.original.errors[i][0]);
                                if (response.data.original.data !== undefined) {
                                    this.messagesData.push(response.data.original.data[i][0]);
                                }
                            }
                        }
                        this.$store.commit('updateLoaderCount', -1);
                    });
            },
            reloadEchoService: function() {
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: process.env.MIX_PUSHER_APP_KEY,
                    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
                    encrypted: false,
                    authEndpoint: 'api/broadcasting/auth',
                    auth: {
                        headers: {
                            Authorization: 'Bearer ' + localStorage.getItem('accessToken'),
                        }
                    }
                });
            },
        },
    }
    import Echo from 'laravel-echo';
</script>
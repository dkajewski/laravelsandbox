<template>
    <div class="container">
        <div class="menu-placeholder"></div>
        <div class="col-md-12">
            <h1>{{ $t('basic.register') }}</h1>
            <div class="card">
                <div class="card-body">
                    <div>
                        <form @submit="register">
                            <div class="form-group">
                                <input type="text" class="form-control" v-model="name" :placeholder="$t('basic.name')"/>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" v-model="email" :placeholder="$t('basic.email')"/>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" v-model="password" :placeholder="$t('basic.password')"/>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" v-model="password_confirmation" :placeholder="$t('basic.password-confirm')"/>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-primary">
                                    {{ $t('basic.register') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="error" v-show="error">
                        <p v-for="errorMessage in messages">{{ errorMessage }}</p>
                    </div>
                    <div class="success" v-show="success">
                        <p v-for="successMessage in messages">{{ $t(successMessage) }}</p>
                        <p><a href="/login">{{ $t('basic.login-perform') }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                name: null,
                email: null,
                password: null,
                password_confirmation: null,
                error: false,
                success: false,
                messages: [],
            };
        },

        computed: {
            token() {
                return this.$store.state.token;
            }
        },

        methods: {
            register (e) {
                e.preventDefault();
                let formData = {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                };

                let self = this;
                axios.post('/api/auth/signUp', formData)
                    .then(function (response) {
                        self.error = false;
                        self.success = false;
                        self.messages = [];
                        if (response.data.success) {
                            self.success = true;
                            self.messages.push(response.data.message);
                        } else {
                            self.error = true;
                            for (let i in response.data.original.errors) {
                                self.messages.push(response.data.original.errors[i][0]);
                            }
                        }
                    });
            }
        }
    }
</script>
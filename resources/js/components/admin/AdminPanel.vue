<template>
    <div class="container">
        <div class="menu-placeholder"></div>
        <admin-sidebar></admin-sidebar>
        <div class="admin-panel-content container">
            <div class="error" v-show="error">
                <p v-for="message in messages">{{$t(message)}}</p>
            </div>
            <div></div>
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                error: false,
                messages: [],
            }
        },

        computed: {
            token() {
                return this.$store.state.token;
            }
        },

        methods: {
            authorizeAdmin () {
                let self = this;
                let params = {
                    headers: {
                        'Authorization': 'Bearer '+localStorage.getItem('accessToken')
                    }
                };
                axios.get('/api/admin/authorizeAdmin', params)
                    .then(function (response) {
                        self.messages = [];
                        self.error = false;
                        if (response.data.success) {

                        } else {
                            self.error = true;
                            if (response.data.message) {
                                self.messages.push(response.data.message);
                            } else {
                                self.messages.push('basic.admin-privileges-not-found');
                            }
                        }
                    });
            }
        },

        beforeMount () {
            this.authorizeAdmin();
        }
    }
</script>
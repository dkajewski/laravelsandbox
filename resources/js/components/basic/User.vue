<template>
    <div>
        <div class="menu-placeholder"></div>
        <div class="d-flex">
            <game-sidebar></game-sidebar>
            <div id="page-content-wrapper" class="container-fluid">
                <div class="error" v-show="error">
                    <p>{{ $t(message) }}</p>
                </div>
                <div v-show="!error">
                    <div class="text-center py-3 avatar-container" v-show="user.avatar.length > 3">
                        <img :src="user.avatar" :alt="user.name"/>
                    </div>
                    <div class="text-center big-icon py-3" v-show="user.avatar.length <= 3">
                        <font-awesome-icon :icon="['fas', 'user']"></font-awesome-icon>
                    </div>
                    <div class="text-center">
                        <h3>{{ user.name }}</h3>
                    </div>
                    <div class="text-center">
                        <font-awesome-icon
                                :icon="['fas', 'comment-dots']"
                                class="user-profile-action-icons"
                                :title="$t('basic.send-a-message')"
                                data-toggle="modal"
                                data-target="#send-a-message-modal">
                        </font-awesome-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="send-a-message-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">{{$t('basic.send-a-message-to')+' '+this.user.name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="sendMessageError = false; sendMessageSuccess = false">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p v-show="sendMessageError !== false" class="error">{{$t(sendMessageError)}}</p>
                        <p v-show="sendMessageSuccess !== false" class="success">{{$t(sendMessageSuccess)}}</p>
                        <textarea v-model="newMessage" v-show="!sendMessageSuccess" :placeholder="$t('basic.message-text')" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="sendMessageError = false; sendMessageSuccess = false">{{$t('basic.close')}}</button>
                        <button type="button" class="btn btn-primary" @click="sendMessage">{{$t('basic.send-a-message')}}</button>
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
                user: {
                    id: '',
                    name: '',
                    avatar: '',
                },
                newMessage: '',
                sendMessageError: false,
                sendMessageSuccess: false,
                error: false,
                message: '',
            }
        },

        created () {
            this.fetchUser();
        },

        methods: {
            fetchUser: function() {
                this.$store.commit('updateLoaderCount', 1);
                let params = {
                    id: this.$route.params.id
                };
                axios.post('/api/user/getUserData', params).then(response => {
                    if (response.data.success) {
                        this.error = false;
                        this.user.avatar = '../'+response.data.data.avatar;
                        this.user.name = response.data.data.name;
                        this.user.id = response.data.data.id;
                    } else if (response.data.message) {
                        this.error = true;
                        this.message = response.data.message;
                    } else {
                        this.error = true;
                        this.message = 'basic.access-forbidden';
                    }

                    this.$store.commit('updateLoaderCount', -1);
                });
            },

            sendMessage: function() {
                if (this.user.id === parseInt(localStorage.getItem('userId'))) {
                    this.sendMessageError = 'basic.message-cant-be-send-to-yourself';
                    return;
                }

                let params = {
                    message: this.newMessage,
                    receiverId: this.user.id,
                };
                this.$store.commit('updateLoaderCount', 1);
                axios.post('/api/message/sendMessage', params).then(response => {
                    if (response.data.success) {
                        this.newMessage = '';
                        this.sendMessageError = false;
                        this.sendMessageSuccess = response.data.message;
                    } else {
                        this.sendMessageError = response.data.message;
                        this.sendMessageSuccess = false;
                    }

                    this.$store.commit('updateLoaderCount', -1);
                });

            },
        },
    }
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUser, faCommentDots } from '@fortawesome/free-solid-svg-icons'
    library.add(faUser, faCommentDots);
</script>

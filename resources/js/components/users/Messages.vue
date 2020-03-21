<template>
    <div>
        <div class="menu-placeholder col-md-12"></div>
        <div class="d-flex">
            <div class="col-md-3 d-none d-md-block">
                <div class="conversations">
                    <div v-show="conversations.length === 0" class="text-center list-group">
                        {{$t('basic.no-conversations')}}
                    </div>
                    <div class="list-group list-group-flush" v-for="conversation in conversations">
                        <div class="list-group-item list-group-item-action conversation-item" :class="{'not-read': conversation.receiver_read === 0 && parseInt(conversation.receiver_id) === parseInt(userId)}" @click="openChat(conversation.to)">
                            <div v-show="conversation.avatar.length" class="conversation-image"><img :src="conversation.avatar" :alt="conversation.name"/></div>
                            <div class="conversation-no-image-container" v-show="!conversation.avatar.length">
                                <div class="conversation-no-image"><font-awesome-icon :icon="['fas', 'user-circle']"></font-awesome-icon></div>
                            </div>
                            <div class="conversation-header pl-3">
                                <div class="username">{{conversation.name}}</div>
                                <div class="message">{{conversation.message}}</div>
                                <div class="date">{{dateFilter(conversation.created_at)}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 border-right border-left conversation-container">
                <div class="d-flex d-md-none text-center mobile-conversation-menu">
                    <div class="col-sm-6">
                        <font-awesome-icon :icon="['fas', 'comments']"
                                           data-toggle="modal"
                                           data-target="#mobile-conversations">
                        </font-awesome-icon>
                    </div>
                    <div class="col-sm-6">
                        <font-awesome-icon :icon="['fas', 'users']"
                                           data-toggle="modal"
                                           data-target="#mobile-friends">
                        </font-awesome-icon>
                    </div>
                </div>
                <div class="conversation-with" v-show="selectedConversationUser.name.length">
                    <div class="d-flex">
                        <div class="conversation-image" v-show="selectedConversationUser.avatar.length">
                            <img :src="selectedConversationUser.avatar" :alt="selectedConversationUser.name"/>
                        </div>
                        <div class="conversation-no-image-container" v-show="!selectedConversationUser.avatar.length">
                            <div class="conversation-no-image"><font-awesome-icon :icon="['fas', 'user-circle']"></font-awesome-icon></div>
                        </div>
                        <div class="conversation-header py-3 px-3">
                            <div class="username">{{selectedConversationUser.name}}</div>
                        </div>
                    </div>
                </div>
                <div class="text-center" v-show="!selectedConversation">
                    {{$t('basic.no-conversation-selected')}}
                </div>
                <div class="conversation-messages-container">
                    <div class="messages-overflow">
                        <div class="conversation-content">
                            <div v-show="selectedConversation" v-for="message in conversationMessages[selectedConversation]">
                                <div class="message-container row">
                                    <div class="message-box" :class="{'my-message': parseInt(message.sender_id) === parseInt(userId)}" :title="dateFilter(message.created_at)">
                                        {{message.message}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="send-message col-md-12 row" v-show="selectedConversation">
                    <div class="col-md-9">
                        <input v-model="messageText" v-on:keyup.enter="sendMessage" class="form-control" :placeholder="$t('basic.enter-message')"/>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary form-control" @click="sendMessage">{{$t('basic.send')}}</button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                friends
            </div>
            <div class="modal left fade d-md-none" id="mobile-conversations" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <button type="button" class="close mobile-close-left" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="conversations">
                            <div v-show="conversations.length === 0" class="text-center list-group">
                                {{$t('basic.no-conversations')}}
                            </div>
                            <div class="list-group list-group-flush" v-for="conversation in conversations">
                                <div class="list-group-item list-group-item-action conversation-item" data-dismiss="modal" :class="{'not-read': conversation.receiver_read === 0 && parseInt(conversation.receiver_id) === parseInt(userId)}" @click="openChat(conversation.to)">
                                    <div v-show="conversation.avatar.length" class="conversation-image"><img :src="conversation.avatar" :alt="conversation.name"/></div>
                                    <div class="conversation-no-image-container" v-show="!conversation.avatar.length">
                                        <div class="conversation-no-image"><font-awesome-icon :icon="['fas', 'user-circle']"></font-awesome-icon></div>
                                    </div>
                                    <div class="conversation-header pl-3">
                                        <div class="username">{{conversation.name}}</div>
                                        <div class="message">{{conversation.message}}</div>
                                        <div class="date">{{dateFilter(conversation.created_at)}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal left fade d-md-none" id="mobile-friends" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <button type="button" class="close mobile-close-left" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div>friends</div>
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
                userId: localStorage.getItem('userId'),
                conversations: [],
                selectedConversation: false,
                selectedConversationUser: {
                    name: '',
                    avatar: '',
                },
                messageText: '',
                conversationMessages: [],
            }
        },
        created() {
            this.fetchConversations();
            window.Echo.join('messages-channel-'+this.userId)
                .listen('.messages-event', (e) => {
                    this.updateConversations(e.message);
                    this.updateConversationMessages(e.message);
                });
        },

        methods: {
            fetchConversations: function() {
                this.$store.commit('updateLoaderCount', 1);
                axios.get('/api/message/fetchConversations').then(response => {
                    this.conversations = response.data.conversations;
                    this.$store.commit('updateLoaderCount', -1);
                });
            },

            fetchMessages: function (id) {
                this.$store.commit('updateLoaderCount', 1);
                let params = {
                    chatWith: id,
                };
                axios.post('/api/message/fetchMessages', params).then(response => {
                    this.conversationMessages[id] = response.data.messages.sort(this.compare);
                    this.loadChat(id);
                    this.$store.commit('updateLoaderCount', -1);
                });
            },

            sendMessage: function() {
                if (!this.messageText) {
                    return;
                }

                let newMessage = {
                    id: '',
                    sender_id: this.userId,
                    receiver_id: this.selectedConversation,
                    message: this.messageText,
                    created_at: new Date(),
                };

                let params = {
                    message: this.messageText,
                    receiverId: this.selectedConversationUser.to,
                };

                this.conversationMessages[this.selectedConversation].push(newMessage);
                axios.post('/api/message/sendMessage', params).then(response => {
                    if (response.data.success) {
                        for (let i in this.conversationMessages[this.selectedConversation]) {
                            if (this.conversationMessages[this.selectedConversation][i].id === '') {
                                this.conversationMessages[this.selectedConversation][i] = response.data.data;
                                response.data.data.to = response.data.data.receiver_id;
                                this.updateConversations(response.data.data);
                            }
                        }
                    } else {
                        //@todo: add asking for resend message on fail
                    }
                });

                this.messageText = '';
            },

            openChat: function(id) {
                this.markConversationAsRead(id);
                if (this.conversationMessages[id] === undefined) {
                    this.fetchMessages(id);
                } else {
                    this.loadChat(id);
                }
            },

            loadChat: function(id) {
                this.selectedConversation = id;
                for (let user in this.conversations) {
                    if (parseInt(this.conversations[user].to) === parseInt(id)) {
                        this.selectedConversationUser = this.conversations[user];
                    }
                }

            },

            compare: function (a, b) {
                if (a.id < b.id) {
                    return -1;
                }

                if (a.id > b.id) {
                    return 1;
                }

                return 0;
            },

            reversedCompare: function (a, b) {
                if (a.id > b.id) {
                    return -1;
                }

                if (a.id < b.id) {
                    return 1;
                }

                return 0;
            },

            dateFilter: function(date) {
                let messageDate = new Date(date);
                let day = messageDate.getDate() < 10 ? '0' + messageDate.getDate() : messageDate.getDate();
                let month = messageDate.getMonth() < 9 ? '0' + (messageDate.getMonth()+1) : (messageDate.getMonth()+1);
                let hours = messageDate.getHours < 10 ? '0' + messageDate.getHours() : messageDate.getHours();
                let minutes = messageDate.getMinutes() < 10 ? '0' + messageDate.getMinutes() : messageDate.getMinutes();

                return day + '.' + month + '.' + messageDate.getFullYear() + ' '
                    + hours + ':' + minutes;
            },

            updateConversations: function(message) {
                console.log(message);
                for (let i in this.conversations) {
                    if (this.conversations[i].to === message.to) {
                        this.conversations[i].id = message.id;
                        this.conversations[i].message = message.message;
                        this.conversations[i].created_at = message.created_at;
                        if (this.selectedConversation === message.to) {
                            this.markConversationAsRead(message.to);
                            this.conversations[i].receiver_read = 1;
                        } else {
                            this.conversations[i].receiver_read = 0;
                        }
                    }
                }

                this.conversations.sort(this.reversedCompare);
            },

            updateConversationMessages: function(message) {
                if (this.conversationMessages[message.to]) {
                    let newMessage = {
                        created_at: message.created_at,
                        id: message.id,
                        message: message.message,
                        receiver_id: message.receiver_id,
                        sender_id: message.sender_id,
                    };

                    this.conversationMessages[message.to].push(newMessage);
                }
            },

            markConversationAsRead: function(conversationWith) {
                for (let i in this.conversations) {
                    if (this.conversations[i].to === conversationWith) {
                        this.conversations[i].receiver_read = 1;
                        let params = {
                            senderId: conversationWith
                        };
                        axios.post('/api/message/markAsRead', params);
                    }
                }
            },
        }
    }
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUserCircle} from '@fortawesome/free-solid-svg-icons'
    import {faUsers} from '@fortawesome/free-solid-svg-icons'
    import {faComments} from '@fortawesome/free-solid-svg-icons'
    library.add(faUserCircle);
    library.add(faUsers);
    library.add(faComments);
</script>

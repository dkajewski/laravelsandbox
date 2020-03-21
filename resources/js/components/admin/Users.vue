<template>
    <div class="wrapper">
        <loading :active.sync="isLoading"></loading>
        <admin-sidebar></admin-sidebar>
        <div class="admin-panel-content container">
            <h1>{{$t('basic.users-list')}}</h1>
            <div class="error" v-show="error">
                <p v-for="message in messages">{{$t(message)}}</p>
            </div>
            <div>
                <table class="table table-striped">
                    <tr>
                        <th>#</th>
                        <th>{{$t('basic.name')}}</th>
                        <th>{{$t('basic.email')}}</th>
                        <th>{{$t('basic.action')}}</th>
                    </tr>
                    <tr v-for="(user, index) in users">
                        <td>{{index+1}}</td>
                        <td>{{user.name}}</td>
                        <td>{{user.email}}</td>
                        <td>
                            <button type="button" class="btn btn-info">
                                {{$t('basic.edit')}}
                            </button>
                        </td>
                    </tr>
                </table>
                <nav aria-label="">
                    <ul class="pagination">
                        <li v-bind:class="[{disabled: !pagination.prev_page_url}]" class="page-item">
                            <a @click="getUsersList(pagination.prev_page_url)" class="page-link" href="#">{{$t('basic.previous')}}</a>
                        </li>
                        <li class="page-item disabled">
                            <a class="page-link text-dark" href="#">{{$t('basic.page')}} {{ pagination.current_page }} {{$t('basic.of-pagination')}} {{ pagination.last_page}}</a>
                        </li>
                        <li v-bind:class="[{disabled: !pagination.next_page_url}]" class="page-item">
                            <a @click="getUsersList(pagination.next_page_url)" class="page-link" href="#">{{$t('basic.next')}}</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                error: false,
                messages: [],
                users: [],
                pagination: {},
                isLoading: false,
            };
        },

        methods: {
            getUsersList: function (pageUrl = '/api/admin/getUsersList') {
                this.setLoadingScreenStatus();
                let self = this;
                let params = {
                    headers: {
                        'Authorization': 'Bearer '+localStorage.getItem('accessToken')
                    }
                };

                axios.get(pageUrl, params)
                    .then(function(response) {
                        self.messages = [];
                        if (response.data.data.length) {
                            self.error = false;
                            self.users = response.data.data;
                            self.makePagination(response.data.meta, response.data.links);
                        } else {
                            self.error = true;
                            if (response.data.message) {
                                self.messages.push(response.data.message);
                            } else {
                                self.messages.push('basic.admin-privileges-not-found');
                            }
                        }

                        self.setLoadingScreenStatus(-1);
                    });
            },

            makePagination(meta, links) {
                this.pagination = {
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    next_page_url: links.next,
                    prev_page_url: links.prev
                };
            },

            setLoadingScreenStatus: function(increment = 1) {
                this.loadingCount += increment;
                this.isLoading = !!this.loadingCount;
            },
        },

        beforeMount () {
            this.getUsersList();
        }
    }
</script>
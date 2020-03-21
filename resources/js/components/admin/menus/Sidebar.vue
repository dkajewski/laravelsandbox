<template>
    <div>
        <loading :active.sync="isLoading"></loading>
        <sidebar-menu :collapsed="true" :menu="menu">
            <span slot="collapse-icon">
            <font-awesome-icon :icon="['fas', 'arrows-alt-h']"></font-awesome-icon>
        </span>
        </sidebar-menu>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                menu: [],
                loadingCount: 0,
                isLoading: false,
            }
        },

        methods: {
            setMenuCategories: function() {
                this.setLoadingScreenStatus();
                let headers = {
                    headers: {
                        'Authorization': 'Bearer '+localStorage.getItem('accessToken')
                    }
                };
                let self = this;
                axios.get('/api/admin/getAdminMenuCategories', headers)
                    .then(function (response) {
                        if (response.data.success) {
                            for (let i in response.data.menu) {
                                self.menu.push(response.data.menu[i]);
                            }
                        }

                        self.setLoadingScreenStatus(-1);
                    });
            },

            setLoadingScreenStatus: function(increment = 1) {
                this.loadingCount += increment;
                this.isLoading = !!this.loadingCount;
            },
        },

        beforeMount() {
            this.setMenuCategories();
        }
    }
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUserSecret, faArrowsAltH, faJedi } from '@fortawesome/free-solid-svg-icons'
    library.add(faUserSecret, faArrowsAltH, faJedi);
</script>
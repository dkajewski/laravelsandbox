<template>
    <div>
        <loading :active.sync="enableLoader"></loading>
        <navbar></navbar>
        <router-view></router-view>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                enableLoader: false,
            }
        },
        watch: {
            '$store.state.loaderCount': function () {
                this.enableLoader = !!this.$store.state.loaderCount;
            }
        },
    };
</script>
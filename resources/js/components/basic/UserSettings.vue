<template>
    <div class="container">
        <div class="menu-placeholder"></div>
        <div class="success" v-show="success">
            <p>{{ $t(message) }}</p>
        </div>
        <div class="error" v-show="error">
            <p>{{ $t(message) }}</p>
        </div>
        <h1>{{ $t('basic.settings') }}</h1>
        <form @submit="saveProfile">
            <div class="row form-group">
                <div class="col-md-3">
                    <label>{{ $t('basic.change-your-avatar') }}</label>
                </div>
                <div class="col-md-9">
                    <div class="col-md-3" v-if="image">
                        <img :src="image" class="img-responsive" height="70" width="90" :alt="$t('basic.avatar')"/>
                    </div>
                    <div class="col-md-9">
                        <input type="file" class="form-control-file" v-on:change="onImageChange"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3 col-form-label">
                    <label for="current-password">{{ $t('basic.enter-current-password') }}</label>
                </div>
                <div class="col-md-9">
                    <input type="password" v-model="currentPassword" id="current-password" name="current-password" class="form-control" :placeholder="$t('basic.current-password')"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3 col-form-label">
                    <label for="password">{{ $t('basic.change-your-password') }}</label>
                </div>
                <div class="col-md-9">
                    <input type="password" v-model="newPassword" id="password" name="password" class="form-control" :placeholder="$t('basic.password')"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3 col-form-label">
                    <label for="password-confirm">{{ $t('basic.password-confirm') }}</label>
                </div>
                <div class="col-md-9">
                    <input type="password" v-model="confirmation" id="password-confirm" class="form-control" name="password-confirm" :placeholder="$t('basic.password-confirm')"/>
                </div>
            </div>
            <div class="form-group d-flex justify-content-center">
                <button class="btn btn-primary">
                    {{ $t('basic.save-profile') }}
                </button>
            </div>
        </form>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                image: '',
                newPassword: '',
                confirmation: '',
                currentPassword: '',
                success: false,
                error: false,
                message: '',
            }
        },

        methods: {
            saveProfile: function (e) {
                this.success = false;
                this.error = false;
                this.$store.commit('updateLoaderCount', 1);
                e.preventDefault();
                let headers = {
                    'Authorization': 'Bearer '+localStorage.getItem('accessToken'),
                };
                let formData = {
                    image: this.image,
                    newPassword: this.newPassword,
                    confirmation: this.confirmation,
                    currentPassword: this.currentPassword,
                };

                axios.post('/api/user/saveProfile', formData, {headers: headers}).then(response => {
                    if (response.data.success) {
                        this.success = true;
                        this.message = response.data.message;
                    } else {
                        this.error = true;
                        this.message = response.data.message;
                    }
                    this.$store.commit('updateLoaderCount', -1);
                });
            },

            onImageChange(e) {
                let files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                this.createImage(files[0]);
            },
            createImage(file) {
                let reader = new FileReader();
                let vm = this;
                reader.onload = (e) => {
                    vm.image = e.target.result;
                };
                reader.readAsDataURL(file);
            },
        }
    }
</script>

<style lang="scss">
.memsource-form {
    max-width: 16em;
    text-align: center;
    margin: 1.5em auto 0.5em auto;
}
</style>

<template>
    <form class="memsource-form" @submit="submit">
        <div class="field field-content">
            <input
                class="input"
                type="text"
                name="username"
                autocomplete="section-memsource username"
                placeholder="Username"
                required="true"
                v-model="username"
            >
            <div class="field-icon">
                <i class="icon fa fa-user"></i>
            </div>
        </div>
        <div class="field field-content">
            <input
                class="input"
                type="password"
                name="password"
                autocomplete="section-memsource current-password"
                placeholder="Password"
                required="true"
                v-model="password"
            >
            <div class="field-icon">
                <i class="icon fa fa-key"></i>
            </div>
        </div>

        <Info v-if="error" type="error">
            <p>{{ error }}</p>
        </Info>

        <p v-if="$store.state.session">
            Hello, {{ $store.state.session.user.firstName }}
        </p>

        <button class="btn btn-rounded btn-positive">Authorize</button>
    </form>
</template>

<script>
var axios = require('axios');

module.exports = {
    components: {
        Info: require('./Info.vue')
    },
    data: function () {
        return {
            username: null,
            password: null,
            error: null
        };
    },
    methods: {
        submit: function (event) {
            event.preventDefault();

            var self = this;

            this.error = null;

            axios.post('https://cloud.memsource.com/web/api2/v1/auth/login', {
                userName: this.username,
                password: this.password
            }).then(function (response) {
                self.$store.commit('SET_SESSION', response.data);
            }).catch(function (error) {
                console.log(arguments);
                var data = error.response && error.response.data,
                    message = 'Error while logging in.';

                if (data) {
                    if (data.errorDescription) {
                        message = data.errorDescription;
                    } else if (data.errorCode) {
                        message = data.errorCode;

                        switch (data.errorCode) {
                            case 'AuthInvalidCredentials': message = 'Invalid credentials.'; break;
                        }
                    }
                } else {
                    message = (error && error.message) || error;
                }

                self.error = message;
            });
        }
    }
};
</script>
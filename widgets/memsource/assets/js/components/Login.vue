<template>
    <form class="ms-form ms-wrapper" @submit="submit">
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
            {{ error }}
        </Info>

        <button class="btn btn-rounded btn-positive">Authorize</button>
    </form>
</template>

<script>
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
            this.$emit('logIn', {
                username: this.username,
                password: this.password
            });
        }
    }
};
</script>
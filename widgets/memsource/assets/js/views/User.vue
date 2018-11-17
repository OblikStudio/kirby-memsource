<style lang="scss">
.ms-user {
    h3 {
        margin-bottom: 1em;
    }

    .user-info {
        margin-bottom: 1.5em;
        line-height: 1.4;
    }

    button {
        display: block;
        min-width: 7em;
        margin: 0 auto;
    }
}
</style>

<template>
    <div class="ms-wrapper ms-user">
        <div class="user-info"> 
            <h3>{{ capitalize(user.role).replace('_', ' ') }}</h3>
            <p>{{ user.firstName }} {{ user.lastName }}</p>
            <p>{{ user.email }}</p>
        </div>

        <button class="btn btn-rounded btn-negative" @click="$emit('logOut')">Log out</button>
    </div>
</template>

<script>
module.exports = {
    computed: {
        user: function () {
            return this.$store.state.session.user;
        }
    }
};
</script>
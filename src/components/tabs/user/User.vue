<template>
	<Info v-if="$store.state.session" :data="$store.state.session.user"></Info>
	<Login v-else></Login>
</template>

<script>
import Info from './views/Info.vue'
import Login from './views/Login.vue'

export default {
	components: {
		Info,
		Login
	}
}
</script>

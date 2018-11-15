<style lang="scss">
.ms-stats {
    margin-bottom: 1.5em;

    & > div {
        display: flex;
        align-items: center;
        margin: 0.3em 0;

        hr {
            flex: 1 0 auto;
            margin: 2px 0.8em 0;
        }
    }
}
</style>

<template>
    <div class="ms-stats">
        <div v-for="stat in items" :key="stat.title">
            <span>{{ stat.title }}</span>
            <hr>
            <span :title="stat.hoverTitle">{{ stat.value }}</span>
        </div>
    </div>
</template>

<script>
module.exports = {
    props: {
        items: {
            type: Array
        }
    }
};
</script>
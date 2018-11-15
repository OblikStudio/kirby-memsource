<style lang="scss">
.ms-import {
    h4 {
        font-size: 1.4em;
    }

    .ms-stats {
        margin: 1.5em 0;
    }
}
</style>

<template>
    <div class="ms-wrapper ms-import">
        <h4>{{ job.filename }}</h4>

        <Stats :items="stats"></Stats>
        
        <button class="btn btn-rounded btn-positive" @click="$emit('import')">
            Import
        </button>
    </div>
</template>

<script>
module.exports = {
    components: {
        Stats: require('../components/Stats.vue')
    },
    computed: {
        job: function () {
            return this.$store.state.job;
        },
        stats: function () {
            return [
                {
                    title: 'Language',
                    value: this.job.targetLang
                },
                {
                    title: 'Job Status',
                    value: this.capitalize(this.job.status)
                },
                {
                    title: 'Date Created',
                    value: this.dateFormat(this.job.dateCreated, 'mmm dd, HH:MM'),
                    hoverTitle: this.dateFormat(this.job.dateCreated, 'dddd, mmmm dS, yyyy, HH:MM:ss')
                }
            ];
        }
    }
};
</script>
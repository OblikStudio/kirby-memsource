<template>
    <div class="ms-wrapper">
        <div v-if="jobs.length" class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="job in jobs" class="dashboard-item job">
                    <button @click="$emit('openJob', job)">
                        <span class="dashboard-item-icon dashboard-item-icon-with-border" title="Target language">
                            <span class="lang-container">
                                <span :class="{
                                    lang: true,
                                    'is-valid': isKirbyLanguage(job.targetLang)
                                }">
                                    {{ job.targetLang }}
                                </span>
                            </span>
                        </span>
                        <p class="dashboard-item-text">
                            <span class="title">
                                {{ job.filename }}
                            </span>

                            <span class="item-time" :title="dateFormat(job.dateCreated, 'dddd, mmmm dS, yyyy, HH:MM:ss')">
                                {{ dateFormat(job.dateCreated, 'mmm dd') }}
                            </span>
                        </p>
                    </button>
                </li>
            </ul>
        </div>
        <Info v-else type="error">
            No jobs found.
        </Info>
    </div>
</template>

<script>
module.exports = {
    components: {
        Info: require('../components/Info.vue')
    },
    computed: {
        jobs: function () {
            return this.$store.state.memsource.jobs;
        }
    },
    methods: {
        isKirbyLanguage: function (input) {
            return this.$store.getters.availableLanguages.find(function (lang) {
                return lang.locale === input;
            });
        }
    }
};
</script>
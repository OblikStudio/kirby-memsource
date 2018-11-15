<template>
    <div class="ms-wrapper">
        <div v-if="$store.state.memsource.jobs.length" class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="job in $store.state.memsource.jobs" class="dashboard-item job">
                    <button @click="$emit('openJob', job)">
                        <span class="dashboard-item-icon dashboard-item-icon-with-border" title="Target language">
                            <span class="lang-container">
                                <span :class="{
                                    lang: true,
                                    'is-valid': isLanguageSupported(job.targetLang)
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
    }
};
</script>
<style lang="scss">
.ms-projects {
    &.ms-wrapper {
        max-width: 25em;
    }

    .dashboard-box {
        margin-bottom: 0;
    }
}
</style>

<template>
    <dir class="ms-projects ms-wrapper">
        <div v-if="$store.state.memsource.projects.length" class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="project in $store.state.memsource.projects" class="dashboard-item">
                    <button @click="$emit('selectProject', project)">
                        <span class="dashboard-item-icon dashboard-item-icon-with-border" title="Source language">
                            <span class="lang-container">
                                <span
                                    class="lang"
                                    :class="{
                                        'is-valid': ($store.getters.siteLanguage.locale === project.sourceLang)
                                    }"
                                >
                                    {{ project.sourceLang }}
                                </span>
                            </span>
                        </span>
                        <p class="dashboard-item-text">
                            <span class="title">
                                {{ project.name }}
                            </span>

                            <span class="item-languages" title="Target languages">
                                <span
                                    v-for="lang in project.targetLangs"
                                    class="lang"
                                    :class="{
                                        'is-valid': isLanguageSupported(lang)
                                    }"
                                >
                                    {{ lang }}
                                </span>
                            </span>
                        </p>
                    </button>
                </li>
            </ul>
        </div>
        <Info v-else type="error">
            No projects found.
        </Info>
    </dir>
</template>

<script>
module.exports = {
    components: {
        Info: require('./Info.vue')
    }
};
</script>
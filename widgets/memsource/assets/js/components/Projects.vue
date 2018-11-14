<style lang="scss">
@import 'vars';

.ms-projects {
    max-width: 25em;
    margin: 0 auto;

    .dashboard-box {
        margin-bottom: 0;
    }

        .project {
            transition: background 0.1s ease;

            &:hover {
                background: #f2f2f2;
            }

            button {
                width: 100%;
                background: none;
                border: none;
                appearance: none;
                outline: none;
                cursor: pointer;
                text-align: left;
            }

                .lang-container {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100%;
                }

                .lang {
                    line-height: 1;
                    font-family: monospace;
                    color: $color-error;

                    &.is-valid {
                        color: $color-success-dark;
                    }
                }

                .targets {
                    float: right;
                    line-height: 1;
                    margin-right: 0.4em;
                }
        }
}
</style>

<template>
    <dir class="ms-projects">
        <div v-if="$store.state.memsource.projects.length" class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="project in $store.state.memsource.projects" class="dashboard-item project">
                    <button @click="$emit('selectProject', project)">
                        <span class="dashboard-item-icon dashboard-item-icon-with-border" title="Source language">
                            <span class="lang-container">
                                <span :class="{
                                    lang: true,
                                    'is-valid': ($store.getters.siteLanguage.locale === project.sourceLang)
                                }">
                                    {{ project.sourceLang }}
                                </span>
                            </span>
                        </span>
                        <p class="dashboard-item-text">
                            <span>{{ project.name }}</span>

                            <span class="targets" title="Target languages">
                                <span v-for="lang in project.targetLangs" :class="{
                                    lang: true,
                                    'is-valid': isLanguageSupported(lang)
                                }">
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
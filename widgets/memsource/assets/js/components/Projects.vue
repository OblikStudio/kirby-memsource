<style lang="scss">
@import 'vars';

.ms-projects {
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
        <div v-if="projects.length" class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="project in projects" class="dashboard-item project">
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
        <Info v-else-if="error" type="error">
            {{ error }}
        </Info>
    </dir>
</template>

<script>
var config = require('../config.js');
var axios = require('axios');
var get = require('lodash.get');

var _cache = null;

module.exports = {
    components: {
        Info: require('./Info.vue')
    },
    data: function () {
        return {
            projects: [],
            error: false
        };
    },
    created: function () {
        var self = this;

        if (_cache) {
            this.projects = _cache;
            return;
        }

        this.$store.commit('SET_LOADING', true);
        this.error = false;

        axios.get(config.api + 'projects', {
            params: {
                token: this.$store.getters.token
            }
        }).then(function (response) {
            var projects = get(response, 'data.content');
            if (!projects) {
                projects = [];
            }

            self.projects = projects;
            _cache = projects;
        }).catch(function (error) {
            self.error = self.getErrorMessage(error);
        }).then(function () {
            self.$store.commit('SET_LOADING', false);
        });
    }
};
</script>
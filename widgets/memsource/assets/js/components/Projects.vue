<style lang="scss">
@import 'vars';

.ms-projects {
    h3 {
        margin: 1em;
        text-align: center;
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
                color: #888;
            }

            .targets {
                float: right;
                line-height: 1;
                margin-right: 0.4em;

                .lang {
                    color: $color-success;

                    &.is-invalid {
                        color: $color-error;
                    }
                }
            }
    }
        
}
</style>

<template>
    <dir class="ms-projects">
        <h3>Projects</h3>

        <div class="dashboard-box">
            <ul class="dashboard-items">
                <li v-for="project in projects" class="dashboard-item project">
                    <button>
                        <span class="dashboard-item-icon dashboard-item-icon-with-border" title="Source language">
                            <span class="lang-container">
                                <span class="lang">
                                    {{ project.sourceLang }}
                                </span>
                            </span>
                        </span>
                        <p class="dashboard-item-text">
                            <span>{{ project.name }}</span>

                            <span class="targets" title="Target languages">
                                <span v-for="lang in project.targetLangs" class="lang">
                                    {{ lang }}
                                </span>
                            </span>
                        </p>
                    </button>
                </li>
            </ul>
        </div>

    </dir>
</template>

<script>
var config = require('../config.js');
var axios = require('axios');
var get = require('lodash.get');

module.exports = {
    data: function () {
        return {
            projects: []
        };
    },
    created: function () {
        var self = this;

        axios.get(config.api + 'projects', {
            params: {
                token: this.$store.getters.token
            }
        }).then(function (response) {
            console.log(response);
            var projects = get(response, 'data.content');
                console.log('proj', projects);

            if (projects) {
                self.projects = projects;
            } else {
                self.projects = null;
            }
        });
    }
};
</script>
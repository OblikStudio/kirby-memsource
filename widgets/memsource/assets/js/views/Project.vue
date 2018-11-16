<style lang="scss">
.ms-project {
    button {
        display: block;
        min-width: 8em;
        margin: 0 auto;

        & + button {
            margin-top: 1.5em;
        }
    }
}
</style>

<template>
    <div class="ms-project ms-wrapper">
        <Info v-for="alert in alerts" :type="alert.type">
            {{ alert.text }}
        </Info>

        <button
            class="btn btn-rounded btn-action"
            @click="$emit('export')"
            :disabled="!canExport"
            :class="{
                'btn-disabled': !canExport
            }"
        >
            Export
        </button>

        <button
            class="btn btn-rounded btn-positive"
            @click="$emit('listJobs')"
            :disabled="!canImport"
            :class="{
                'btn-disabled': !canImport
            }"
        >
            Import
        </button>
    </div>
</template>

<script>
const FATAL_ERRORS = {
    export: [
        'SOURCE_MISMATCH',
        'NO_MATCHING'
    ],
    import: [
        'NO_MATCHING'
    ]
};

module.exports = {
    components: {
        Info: require('../components/Info.vue')
    },
    methods: {
        fatalAlertRaised: function (namespace) {
            return this.alerts.find(function (alert) {
                return FATAL_ERRORS[namespace].indexOf(alert.id) >= 0;
            });
        }
    },
    computed: {
        canExport: function () {
            return !this.fatalAlertRaised('export');
        },
        canImport: function () {
            return !this.fatalAlertRaised('import');
        },
        alerts: function () {
            var self = this,
                values = [],
                project = this.$store.state.project,
                siteLanguage = this.$store.getters.siteLanguage,
                availableLanguages = this.$store.getters.availableLanguages,
                sourceLanguage = this.$store.getters.sourceLanguage,
                targetLanguages = this.$store.getters.targetLanguages,
                targetLanguagesMatching = this.$store.getters.targetLanguagesMatching;

            if (sourceLanguage.locale !== project.sourceLang) {
                values.push({
                    id: 'SOURCE_MISMATCH',
                    type: 'error',
                    text: 'Active language "' + sourceLanguage.locale + '" does not match project source language "' + project.sourceLang + '"!'
                });
            }

            if (!targetLanguagesMatching.length) {
                values.push({
                    id: 'NO_MATCHING',
                    type: 'error',
                    text: 'No matching languages!'
                });
            }

            if (values.length) {
                return values;
            }

            var missingFromKirby = project.targetLangs.filter(function (projectLang) {
                return !availableLanguages.find(function (kirbyLang) {
                    return kirbyLang.locale === projectLang;
                });
            });

            var missingFromMemsource = targetLanguages.filter(function (targetLang) {
                return project.targetLangs.indexOf(targetLang.locale) < 0;
            }).map(function (lang) {
                return lang.locale;
            });

            if (missingFromKirby.length) {
                values.push({
                    type: 'warning',
                    text: 'Missing languages in Kirby: ' + missingFromKirby.join(', ') + '!'
                });
            }

            if (missingFromMemsource.length) {
                values.push({
                    type: 'warning',
                    text: 'Missing target languages in Memsource: ' + missingFromMemsource.join(', ') + '!'
                });
            }

            if (siteLanguage.locale !== sourceLanguage.locale) {
                values.push({
                    type: 'info',
                    text: 'You\'ll be exporting the site in "' + sourceLanguage.locale + '" which is not the main site language.'
                });
            }

            return values;
        }
    }
};
</script>
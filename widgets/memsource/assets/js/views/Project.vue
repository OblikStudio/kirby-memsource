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
        'ACTIVE_MISMATCH',
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
            var found = false;

            this.alerts.forEach(function (alert) {
                if (FATAL_ERRORS[namespace].indexOf(alert.id) >= 0) {
                    found = true;
                }
            });

            return found;
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
                activeLanguage = this.$store.getters.activeLanguage,
                kirbyLanguages = this.$store.state.kirby.languages,
                availableLanguages = this.$store.getters.availableLanguages;

            var missingFromKirby = [];
            project.targetLangs.forEach(function (lang) {
                var found = false;

                kirbyLanguages.forEach(function (kirbyLang) {
                    if (kirbyLang.locale === lang) {
                        found = true;
                    }
                });

                if (!found) {
                    missingFromKirby.push(lang);
                }
            });

            var missingFromMemsource = [];
            kirbyLanguages.forEach(function (kirbyLang) {
                if (!kirbyLang.isActive && project.targetLangs.indexOf(kirbyLang.locale) < 0) {
                    missingFromMemsource.push(kirbyLang.locale);
                }
            });

            if (activeLanguage.locale !== project.sourceLang) {
                values.push({
                    id: 'ACTIVE_MISMATCH',
                    type: 'error',
                    text: 'Active language "' + activeLanguage.locale + '" does not match project source language "' + project.sourceLang + '"!'
                });
            }

            if (missingFromKirby.length) {
                values.push({
                    type: 'warning',
                    text: 'Missing languages in Kirby: ' + missingFromKirby.join(', ') + '!'
                });
            }

            if (missingFromMemsource.length) {
                values.push({
                    type: 'warning',
                    text: 'Missing target languages in project: ' + missingFromMemsource.join(', ') + '!'
                });
            }

            if (!availableLanguages.length) {
                values.push({
                    id: 'NO_MATCHING',
                    type: 'error',
                    text: 'No matching languages!'
                });
            }

            if (siteLanguage.locale !== activeLanguage.locale) {
                values.push({
                    type: 'info',
                    text: 'You\'ll be exporting the site in "' + activeLanguage.locale + '" which is not the main site language.'
                });
            }

            return values;
        }
    }
};
</script>
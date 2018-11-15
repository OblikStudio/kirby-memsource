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
        <Info v-for="warning in warnings" type="warning">
            {{ warning }}
        </Info>

        <button
            class="btn btn-rounded btn-action"
            @click="$emit('export')"
            :disabled="!$store.getters.availableLanguages.length"
            :class="{
                'btn-disabled': !$store.getters.availableLanguages.length
            }"
        >
            Export
        </button>

        <button
            class="btn btn-rounded btn-positive"
            @click="$emit('listJobs')"
        >
            Import
        </button>
    </div>
</template>

<script>
module.exports = {
    components: {
        Info: require('../components/Info.vue')
    },
    computed: {
        warnings: function () {
            var self = this,
                values = [],
                project = this.$store.state.project,
                siteLanguage = this.$store.getters.siteLanguage,
                kirbyLanguages = this.$store.state.kirby.languages;

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
                if (!kirbyLang.isDefault && project.targetLangs.indexOf(kirbyLang.locale) < 0) {
                    missingFromMemsource.push(kirbyLang.locale);
                }
            });

            if (siteLanguage.locale !== project.sourceLang) {
                values.push('Site language "' + siteLanguage.locale + '" does not match project source language "' + project.sourceLang + '"!');
            }

            if (missingFromKirby.length) {
                values.push('Missing languages in Kirby: ' + missingFromKirby.join(', ') + '!');
            }

            if (missingFromMemsource.length) {
                values.push('Missing languages in Memsource: ' + missingFromMemsource.join(', ') + '!');
            }

            return values;
        }
    }
};
</script>
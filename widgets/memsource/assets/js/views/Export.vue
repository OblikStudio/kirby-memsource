<style lang="scss">
.ms-export {
    label {
        text-align: left;
    }
}
</style>

<template>
    <div class="ms-export ms-wrapper">
        <Stats :items="stats"></Stats>

        <form @submit="submit">
            <label class="label" for="ms-form-langs">Create job for:</label>
            <div class="field field-content">
                <div class="input input-with-selectbox" :class="{
                    'input-is-focused': isFocused
                }">
                    <div class="selectbox-wrapper">
                        <select
                            id="ms-form-langs"
                            class="selectbox"
                            @focus="isFocused = true"
                            @blur="isFocused = false"
                            required="true"
                            v-model="language"
                        >
                            <option
                                v-for="option in exportLanguage"
                                :key="option.text"
                                :value="option.value"
                            >
                                {{ option.text}}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="field-icon">
                    <i class="icon fa fa-chevron-down"></i>
                </div>
            </div>

            <label class="label" for="ms-form-filename">Job name:</label>
            <div class="field field-content">
                <input
                    id="ms-form-filename"
                    class="input"
                    type="text"
                    name="filename"
                    placeholder="Name"
                    required="true"
                    v-model="filename"
                >
            </div>

            <button class="btn btn-rounded btn-action">Upload</button>
        </form>
    </div>
</template>

<script>
function countObjectData (object) {
    var data = {
        strings: 0,
        chars: 0
    };

    for (var k in object) {
        var value = object[k];

        if (value && typeof value === 'object') {
            var child = countObjectData(value);
            data.strings += child.strings;
            data.chars += child.chars;
        } else {
            value = value + '';

            data.strings++;
            data.chars += value.length;                            
        }
    }

    return data;
}

function getExportStats (data) {
    var stats = countObjectData(data);
    stats.pages = Object.keys(data).length;

    return stats;
}

module.exports = {
    components: {
        Stats: require('../components/Stats.vue')
    },
    data: function () {
        return {
            stats: [],
            isFocused: false,
            language: null,
            filename: 'kirby-site'
        };
    },
    computed: {
        exportLanguage: function () {
            var values = [],
                options = this.$store.getters.targetLanguagesMatching;

            if (options.length) {
                if (options.length > 1) {
                    var locales = options.map(function (lang) {
                        return lang.locale;
                    });

                    values.push({
                        value: locales,
                        text: 'All'
                    });
                }

                options.forEach(function (lang) {
                    values.push({
                        value: lang.locale,
                        text: lang.name
                    });
                });
            }

            if (values.length) {
                this.language = values[0].value;
            }

            return values;
        }
    },
    methods: {
        submit: function (event) {
            event.preventDefault();

            this.$emit('upload', {
                language: this.language,
                filename: this.filename
            });
        },
        updateExportStats: function (data) {
            var stats = getExportStats(data);

            this.stats = [
                {
                    title: 'Pages',
                    value: stats.pages.toLocaleString()
                },
                {
                    title: 'Strings',
                    value: stats.strings.toLocaleString()
                },
                {
                    title: 'Characters',
                    value: stats.chars.toLocaleString()
                }
            ];
        }
    },
    created: function () {
        var content = this.$store.state.pluginApi.exportData;
        this.updateExportStats(content);
    }
};
</script>
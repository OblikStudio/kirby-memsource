<style lang="scss">
.ms-export {
    .stats {
        margin-bottom: 1.5em;
    }

        .stat {
            display: flex;
            align-items: center;
            margin: 0.3em 0;

            hr {
                flex: 1 0 auto;
                margin: 2px 0.8em 0;
            }
        }

    label {
        text-align: left;
    }
}
</style>

<template>
    <div class="ms-export ms-wrapper">
        <div class="stats">
            <div v-for="stat in stats" :key="stat.title" class="stat">
                <span>{{ stat.title }}</span>
                <hr>
                <span>{{ stat.value.toLocaleString() }}</span>
            </div>
        </div>

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
                options = this.$store.getters.availableLanguages;

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
                    value: stats.pages
                },
                {
                    title: 'Strings',
                    value: stats.strings
                },
                {
                    title: 'Characters',
                    value: stats.chars
                }
            ];
        }
    },
    created: function () {
        var content = this.$store.state.exportData;
        this.updateExportStats(content);
    }
};
</script>
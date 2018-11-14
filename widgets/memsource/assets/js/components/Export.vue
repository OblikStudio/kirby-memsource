<style lang="scss">
.ms-export {
    max-width: 16em;
    margin: 0 auto;
    text-align: center;

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

    button {
        margin-top: 1.5em;
    }
}
</style>

<template>
    <div class="ms-export">
        <div class="stats">
            <div v-for="stat in stats" :key="stat.title" class="stat">
                <span>{{ stat.title }}</span>
                <hr>
                <span>{{ stat.value.toLocaleString() }}</span>
            </div>
        </div>

        <form>
            <label class="label" for="ms-export-options">Create job for:</label>
            <div class="field-content">
                <div class="input input-with-selectbox" :class="{
                    'input-is-focused': isFocused
                }">
                    <div class="selectbox-wrapper">
                        <select
                            id="ms-export-options"
                            class="selectbox"
                            @focus="isFocused = true"
                            @blur="isFocused = false"
                            v-model="exportLanguages"
                        >
                            <option
                                v-for="option in exportOptions"
                                :key="option.value"
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

            <button class="btn btn-rounded btn-action">Upload</button>
        </form>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            isFocused: false,
            exportLanguages: '_all',
            stats: [
                {
                    title: 'Pages',
                    value: 37
                },
                {
                    title: 'Strings',
                    value: 438
                },
                {
                    title: 'Characters',
                    value: 12862
                }
            ]
        };
    },
    computed: {
        exportOptions: function () {
            var values = [
                {
                    value: '_all',
                    text: 'All'
                }
            ];

            this.$store.state.kirby.languages.forEach(function (lang) {
                values.push({
                    value: lang.locale,
                    text: lang.name
                });
            });

            return values;
        }
    }
};
</script>
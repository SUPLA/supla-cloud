<template>
    <div>
        <label class="d-flex">
            <span class="flex-grow-1">{{ $t(labelI18n) }}</span>
            <span class="label-hint" v-html="$t('type {code} to show available variables', {code: '<code>{</code>'})"></span>
        </label>
        <Mentionable
            :keys="['{']"
            :items="allVariables"
            insert-space>
            <input type="text" class="form-control" v-model="model" name="notification-title" :disabled="disabled"
                maxlength="100" autocomplete="off">
            <template #no-result>
                <div>{{ $t('No results') }}</div>
            </template>
            <template #item="{ item }">
                <div class="variable-hint">
                    {{ item.label }} <code>{{ '{' + item.value }}</code>
                </div>
            </template>
        </Mentionable>
    </div>
</template>

<script>
    import {Mentionable} from 'vue-mention'

    export default {
        components: {Mentionable},
        props: {
            labelI18n: String,
            value: String,
            disabled: Boolean,
            variables: {
                type: Array,
                default: () => [],
            },
        },
        methods: {
            insertVariable(field, variable) {
                const newValue = this[field] + variable;
                this.change({[field]: newValue});
            },
        },
        computed: {
            model: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            allVariables() {
                return [
                    ...this.variables,
                    {label: 'Date', value: '{date}'}, // i18n,
                    {label: 'Time', value: '{time}'}, // i18n,
                    {label: 'Date and time', value: '{date_time}'}, // i18n,
                ].map(v => ({label: this.$t(v.label), value: v.value.substring(1), searchText: `${this.$t(v.label)} ${v.value}`}))
            }
        },
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    .mention-item {
        padding: 4px 10px;
        border-radius: 4px;
    }

    .mention-selected {
        background: $supla-green;
    }

    .label-hint {
        font-weight: normal;
        font-size: .8em;
        color: $supla-grey-dark;
    }
</style>

<template>
    <div class="form-group m-0">
        <label>{{ $t('Choose the condition') }}</label>
        <select class="form-control" v-model="trigger">
            <option v-for="o in availableOptions" :key="o.label" :value="o.trigger">{{ $t(o.label) }}</option>
        </select>
    </div>
</template>

<script>
    import {isEqual} from "lodash";
    import {DEFAULT_FIELD_NAMES, FIELD_NAMES, STATIC_TRIGGERS} from "@/channels/reactions/trigger-humanizer";

    export default {
        props: {
            value: Object,
            subject: Object,
            options: Array,
        },
        methods: {
            isEqual,
        },
        computed: {
            availableOptions() {
                return this.options || STATIC_TRIGGERS[this.subject.functionId];
            },
            trigger: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            fieldLabel() {
                return this.$t(
                    (this.field && FIELD_NAMES[this.field]) ||
                    DEFAULT_FIELD_NAMES[this.subject.functionId] ||
                    'the value' // i18n
                );
            },
            label() {
                return this.$t('When {field} will be', {field: this.fieldLabel});
            },
        }
    }
</script>

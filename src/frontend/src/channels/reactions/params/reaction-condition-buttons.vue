<template>
    <div class="d-flex align-items-center">
        <label class="flex-grow-1 pr-3">{{ label }}</label>
        <div class="btn-group btn-group-flex">
            <a :class="'btn ' + (isEqual(option.trigger, trigger) ? 'btn-green' : 'btn-default')"
                v-for="option in options"
                :key="option.label"
                @click="trigger = option.trigger">
                {{ $t(option.label) }}
            </a>
        </div>
    </div>
</template>

<script>
    import {isEqual} from "lodash";
    import {DEFAULT_FIELD_NAMES, FIELD_NAMES} from "@/channels/reactions/trigger-humanizer";

    export default {
        props: {
            value: Object,
            subject: Object,
            options: {
                type: Array,
                required: true,
            }
        },
        methods: {
            isEqual,
        },
        computed: {
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

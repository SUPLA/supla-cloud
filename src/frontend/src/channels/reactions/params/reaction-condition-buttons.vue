<template>
    <div class="d-flex align-items-center">
        <label class="flex-grow-1">{{ $t(label) }}</label>
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

    export default {
        props: {
            value: Object,
            label: {
                type: String,
                default: 'When', // i18n
            },
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
            }
        }
    }
</script>

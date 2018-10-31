<template>
    <dl>
        <dd>{{ $t('Unit') }}</dd>
        <dt>
            <span class="input-group">
                <input type="text"
                    maxlength="4"
                    class="form-control text-center"
                    v-model="unitTextInput"
                    @keyup="onKeyUp">
            </span>
        </dt>
    </dl>
</template>

<script>
    export default {
        props: ['channel', 'value', 'default_unit'],
        data() {
            return {
                unit: undefined,
                textInput: undefined
            };
        },
        methods: {
            getDefaultUnit() {
                return this.default_unit ? this.default_unit : 'm³';
            },
            onKeyUp() {
                this.$emit('input', this.unit);
                this.$emit('change');
            }
        },
        computed: {
            unitTextInput: {
                get() {
                    return this.textInput;
                },
                set(value) {
                    this.textInput = value;
                    this.unit = value ? value : this.getDefaultUnit();
                    this.channel.textParam2 = value == this.getDefaultUnit() ? null : value;
                }
            }
        },
        mounted() {
            this.unit = this.channel.textParam2;
            if (!this.unit) {
                this.unit = this.getDefaultUnit();
                this.$emit('input', this.unit);
            }
            this.textInput = this.unit;
        }
    };
</script>

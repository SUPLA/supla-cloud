<template>
    <dl>
        <dd>{{ $t('Unit') }}</dd>
        <dt>
            <span class="input-group">
                <input type="text" maxlength="4"
                    class="form-control text-center"
                    v-model="unitTextInput"
                @keyup="$emit('change')">
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
                    this.$emit('input', this.unit);
                }
            }
        },
        mounted() {
            this.unit = this.channel.textParam2;
            if (!this.unit) {
                this.unit = this.getDefaultUnit();
            }
            this.textInput = this.unit;
        }
    };
</script>

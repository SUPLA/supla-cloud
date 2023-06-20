<template>
    <div class="notification-subject-form">
        <div :class="['form-group', {'has-error': displayValidationErrors && !validTitleAndBody}]">
            <label>{{ $t('Title') }}</label>
            <input type="text" class="form-control" v-model="title">
            <div class="help-block help-error">{{ $t('Notification must have title or body.') }}</div>
        </div>
        <div :class="['form-group', {'has-error': displayValidationErrors && !validTitleAndBody}]">
            <label>{{ $t('Body') }}</label>
            <input type="text" class="form-control" v-model="body">
            <div class="help-block help-error">{{ $t('Notification must have title or body.') }}</div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            value: {type: Object},
            displayValidationErrors: Boolean,
        },
        data() {
            return {};
        },
        mounted() {
            this.validate();
        },
        methods: {
            change(newProps) {
                this.$emit('input', {...this.value, ...newProps});
                this.validate();
            },
            validate() {
                this.$emit('isValid', this.validTitleAndBody);
            }
        },
        computed: {
            title: {
                get() {
                    return this.value?.title || '';
                },
                set(title) {
                    this.change({title});
                }
            },
            body: {
                get() {
                    return this.value?.body || '';
                },
                set(body) {
                    this.change({body});
                }
            },
            validTitleAndBody() {
                return !!(this.title || this.body)
            },
        },
        watch: {
            value() {
                this.validate();
            }
        }
    };
</script>

<style lang="scss" scoped>
    @import "../styles/variables";

    .notification-subject-form {

    }
</style>

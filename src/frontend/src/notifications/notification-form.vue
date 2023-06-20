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
        <div :class="['form-group', {'has-error': displayValidationErrors && !validRecipients}]">
            <label>{{ $t('Recipients') }}</label>
            <AccessIdsDropdown v-model="accessIds"/>
            <div class="help-block help-error">{{ $t('Notification must be sent to someone.') }}</div>
        </div>
    </div>
</template>

<script>
    import AccessIdsDropdown from "@/access-ids/access-ids-dropdown.vue";

    export default {
        components: {AccessIdsDropdown},
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
                this.$emit('isValid', this.validTitleAndBody && this.validRecipients);
            },
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
            accessIds: {
                get() {
                    return this.value?.accessIds || [];
                },
                set(accessIds) {
                    this.change({accessIds});
                }
            },
            validTitleAndBody() {
                return !!(this.title || this.body)
            },
            validRecipients() {
                return this.accessIds.length > 0;
            },
        },
        watch: {
            value() {
                this.validate();
            },
        }
    };
</script>

<style lang="scss" scoped>
    @import "../styles/variables";

    .notification-subject-form {

    }
</style>

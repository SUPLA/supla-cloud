<template>
    <div class="notification-subject-form">
        <div class="form-group">
            <label>{{ $t('Title') }}</label>
            <input type="text" class="form-control" v-model="title" name="notification-title">
        </div>
        <div :class="['form-group', {'has-error': displayValidationErrors && !body}]">
            <label>{{ $t('Body') }}</label>
            <input type="text" class="form-control" v-model="body" name="notification-body">
            <div class="help-block help-error">{{ $t('Notification must have a body.') }}</div>
        </div>
        <div :class="['form-group', {'has-error': displayValidationErrors && !validRecipients}]">
            <label>{{ $t('Recipients') }}</label>
            <AccessIdsDropdown v-model="accessIds"/>
            <div class="help-block help-error">{{ $t('The notification must have a recipient.') }}</div>
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
        methods: {
            change(newProps) {
                this.$emit('input', {...this.value, ...newProps});
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
            accessIds: {
                get() {
                    return this.value?.accessIds?.map(id => ({id})) || [];
                },
                set(accessIds) {
                    this.change({accessIds: accessIds.map(aid => aid.id)});
                }
            },
            validRecipients() {
                return this.accessIds.length > 0;
            },
        },
    };
</script>

<style lang="scss" scoped>
    @import "../styles/variables";

    .notification-subject-form {

    }
</style>

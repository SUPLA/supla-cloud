<template>
    <div class="notification-subject-form">
        <div class="form-group">
            <label>{{ $t('Title') }}</label>
            <input type="text" class="form-control" v-model="title" name="notification-title" :disabled="disableTitleMessage">
            <div class="help-block" v-if="disableTitleMessage">{{ disableTitleMessage }}</div>
        </div>
        <div :class="['form-group', {'has-error': displayValidationErrors && !disableBodyMessage && !body}]">
            <label>{{ $t('Body') }}</label>
            <input type="text" class="form-control" v-model="body" name="notification-body" :disabled="disableBodyMessage">
            <div class="help-block" v-if="disableBodyMessage">{{ disableBodyMessage }}</div>
            <div class="help-block help-error" v-else>{{ $t('Notification must have a body.') }}</div>
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
            disableTitleMessage: String,
            disableBodyMessage: String,
            displayValidationErrors: Boolean,
        },
        data() {
            return {};
        },
        mounted() {
            if (!this.isValid) {
                this.change({});
            }
        },
        methods: {
            change(newProps) {
                const data = {...this.value, ...newProps};
                data.isValid = data.accessIds?.length > 0 && (!!this.disableBodyMessage || !!data.body);
                this.$emit('input', data);
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
            isValid() {
                return this.validRecipients && (!!this.disableBodyMessage || !!this.body);
            },
        },
    };
</script>

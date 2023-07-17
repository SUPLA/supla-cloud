<template>
    <div class="notification-subject-form">
        <div class="form-group">
            <label>{{ $t('Title') }}</label>
            <div :class="{'input-group': variables.length > 0}">
                <input type="text" class="form-control" v-model="title" name="notification-title" :disabled="disableTitleMessage"
                    maxlength="100">
                <div class="input-group-btn" v-if="variables.length">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        {{ $t('Variables') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li v-for="variable in variables" :key="variable.code">
                            <a @click="insertVariable('title', variable.code)">
                                {{ variable.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="help-block" v-if="disableTitleMessage">{{ disableTitleMessage }}</div>
        </div>
        <div :class="['form-group', {'has-error': displayValidationErrors && !disableBodyMessage && !body}]">
            <label>{{ $t('Body') }}</label>
            <div :class="{'input-group': variables.length > 0}">
                <input type="text" class="form-control" v-model="body" name="notification-body" :disabled="disableBodyMessage"
                    maxlength="255">
                <div class="input-group-btn" v-if="variables.length">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        {{ $t('Variables') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li v-for="variable in variables" :key="variable.code">
                            <a @click="insertVariable('body', variable.code)">
                                {{ variable.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
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
            variables: {
                type: Array,
                default: () => [],
            },
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
            },
            insertVariable(field, variable) {
                const newValue = this[field] + variable;
                this.change({[field]: newValue});
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

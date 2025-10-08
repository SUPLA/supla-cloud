<template>
  <div class="notification-subject-form">
    <div class="form-group">
      <NotificationInputWithVariables v-model="title" label-i18n="Title" :disabled="!!disableTitleMessage" :subject="subject" />
      <div v-if="disableTitleMessage" class="help-block">{{ disableTitleMessage }}</div>
    </div>
    <div :class="['form-group', {'has-error': displayValidationErrors && !disableBodyMessage && !body}]">
      <NotificationInputWithVariables v-model="body" label-i18n="Body" :disabled="!!disableBodyMessage" :subject="subject" />
      <div v-if="disableBodyMessage" class="help-block">{{ disableBodyMessage }}</div>
      <div v-else class="help-block help-error">{{ $t('Notification must have a body.') }}</div>
    </div>
    <div :class="['form-group', {'has-error': displayValidationErrors && !validRecipients}]">
      <label>{{ $t('Recipients') }}</label>
      <AccessIdsDropdown v-model="accessIds" />
      <div class="help-block help-error">{{ $t('The notification must have a recipient.') }}</div>
    </div>
  </div>
</template>

<script>
  import AccessIdsDropdown from '@/access-ids/access-ids-dropdown.vue';
  import NotificationInputWithVariables from '@/notifications/notification-input-with-variables.vue';

  export default {
    components: {NotificationInputWithVariables, AccessIdsDropdown},
    props: {
      subject: Object,
      value: {type: Object},
      disableTitleMessage: String,
      disableBodyMessage: String,
      displayValidationErrors: Boolean,
      allowNoRecipients: Boolean,
      variables: {
        type: Array,
        default: () => [],
      },
    },
    data() {
      return {};
    },
    computed: {
      title: {
        get() {
          return this.value?.title || '';
        },
        set(title) {
          this.change({title});
        },
      },
      body: {
        get() {
          return this.value?.body || '';
        },
        set(body) {
          this.change({body});
        },
      },
      accessIds: {
        get() {
          return this.value?.accessIds?.map((id) => ({id})) || [];
        },
        set(accessIds) {
          this.change({accessIds: accessIds.map((aid) => aid.id)});
        },
      },
      validRecipients() {
        return this.allowNoRecipients || this.accessIds.length > 0;
      },
      isValid() {
        return this.validRecipients && (!!this.disableBodyMessage || !!this.body);
      },
      allVariables() {
        return [
          ...this.variables,
          {label: 'Date', value: '{date}'}, // i18n,
          {label: 'Time', value: '{time}'}, // i18n,
          {label: 'Date and time', value: '{date_time}'}, // i18n,
        ].map((v) => ({
          label: this.$t(v.label),
          value: v.value.substring(1),
          searchText: `${this.$t(v.label)} ${v.value}`,
        }));
      },
    },
    mounted() {
      if (!this.isValid) {
        this.change({});
      }
    },
    methods: {
      change(newProps) {
        const data = {...this.value, ...newProps};
        data.isValid = (this.allowNoRecipients || data.accessIds?.length > 0) && (!!this.disableBodyMessage || !!data.body);
        this.$emit('input', data);
      },
    },
  };
</script>

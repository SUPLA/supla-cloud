<template>
  <div>
    <button :class="'devices-registration-button btn btn-outline btn-' + (enabledUntil ? 'orange' : 'grey')" type="button" :disabled="saving" @click="toggle()">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <button-loading-dots v-if="saving"></button-loading-dots>
          <i v-else :class="enabledUntil ? 'pe-7s-attention' : 'pe-7s-close-circle'"></i>
        </div>
        <div>
          <span v-if="saving">{{ $t(captionI18n) }}</span>
          <span v-else
            >{{ $t(captionI18n) }}: <span class="big">{{ enabledUntil ? $t('ACTIVE') : $t('INACTIVE') }}</span></span
          >
          <div v-if="enabledUntil">{{ $t('valid until') }}: {{ enabledUntilCalendar }}</div>
          <div v-if="!saving" class="small text-muted">{{ enabledUntil ? $t('CLICK TO DISABLE') : $t('CLICK TO ENABLE') }}</div>
        </div>
      </div>
    </button>
  </div>
</template>

<script>
  import ButtonLoadingDots from '../../common/gui/loaders/button-loading-dots.vue';
  import {DateTime} from 'luxon';
  import {api} from '@/api/api.js';

  export default {
    components: {ButtonLoadingDots},
    props: ['field', 'captionI18n'],
    data() {
      return {
        saving: false,
        enabledUntil: false,
      };
    },
    computed: {
      enabledUntilCalendar() {
        return this.enabledUntil ? DateTime.fromISO(this.enabledUntil).toLocaleString(DateTime.DATETIME_SHORT) : '';
      },
    },
    mounted() {
      this.loadUserInfo();
    },
    methods: {
      toggle() {
        this.saving = true;
        api
          .patch('users/current', {
            action: 'change:' + this.field,
            enable: !this.enabledUntil,
          })
          .then(({body}) => (this.enabledUntil = body[this.field]))
          .finally(() => (this.saving = false));
      },
      loadUserInfo() {
        this.saving = true;
        api
          .get('users/current')
          .then(({body}) => (this.enabledUntil = body[this.field]))
          .finally(() => (this.saving = false));
      },
    },
  };
</script>

<style lang="scss">
  .devices-registration-button {
    min-height: 62px;
    .table {
      margin: 0;
    }
    i {
      font-size: 3em;
      margin-right: 10px;
    }
    label {
      vertical-align: super;
      margin: 0;
    }
    .vue-switcher {
      vertical-align: text-top;
    }
    .help-block {
      font-size: 0.7em;
    }
  }
</style>

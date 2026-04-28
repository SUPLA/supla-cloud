<template>
  <modal class="text-left modal-800 display-newlines" @confirm="$emit('confirm')">
    <loading-cover :loading="!rules">
      <div v-if="rules" v-html="rules"></div>
    </loading-cover>
  </modal>
</template>

<script>
  import {api} from '@/api/api.js';
  import Modal from '@/common/modal.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';

  export default {
    components: {LoadingCover, Modal},
    data() {
      return {
        rules: '',
      };
    },
    watch: {
      '$i18n.locale'() {
        this.fetch();
      },
    },
    mounted() {
      this.fetch();
    },
    methods: {
      fetch() {
        this.rules = '';
        const rulesLang = this.$i18n.locale == 'pl' ? 'pl' : 'en';
        api.get(`/regulations/rules/rules_${rulesLang}.html`).then((response) => (this.rules = response.body));
      },
    },
  };
</script>

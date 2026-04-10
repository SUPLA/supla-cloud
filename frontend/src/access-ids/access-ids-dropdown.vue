<template>
  <div>
    <SelectForSubjects
      v-model="chosenAids"
      multiple
      class="aid-dropdown"
      :options="aids"
      :caption="aidCaption"
      choose-prompt-i18n="choose the access identifiers"
    />
  </div>
</template>

<script>
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {SelectForSubjects},
    props: {
      value: Array,
    },
    data() {
      return {
        aids: undefined,
      };
    },
    computed: {
      chosenAids: {
        get() {
          if (!this.value || !this.aids) {
            return [];
          } else {
            const ids = this.value.map((a) => a.id);
            return this.aids.filter((aid) => ids.includes(aid.id));
          }
        },
        set(aids) {
          this.$emit('input', aids);
        },
      },
    },
    mounted() {
      this.fetchAids();
    },
    methods: {
      fetchAids() {
        api.get('accessids').then(({body: aids}) => {
          this.aids = aids;
        });
      },
      aidCaption(aid) {
        return (aid.caption || `ID${aid.id}`) + ` (${aid.relationsCount.clientApps})`;
      },
    },
  };
</script>

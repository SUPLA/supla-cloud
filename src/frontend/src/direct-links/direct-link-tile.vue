<template>
  <square-link :class="'clearfix pointer lift-up ' + (model.active ? 'green' : 'grey')" @click="$emit('click')">
    <router-link :to="linkSpec">
      <div class="clearfix">
        <function-icon :model="subject" class="pull-right" width="60"></function-icon>
        <h3>{{ caption }}</h3>
      </div>
      <dl>
        <dd>ID</dd>
        <dt>{{ model.id }}</dt>
        <dd>{{ $t('Last used') }}</dd>
        <dt v-if="model.lastUsed">{{ formatDateTime(model.lastUsed) }}</dt>
        <dt v-else>{{ $t('Never') }}</dt>
        <dd>{{ $t('Subject type') }}</dd>
        <dt>{{ $t('actionableSubjectType_' + model.subjectType) }}</dt>
      </dl>
    </router-link>
  </square-link>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import FunctionIcon from '../channels/function-icon.vue';
  import SquareLink from '@/common/tiles/square-link.vue';
  import {useI18n} from 'vue-i18n';
  import {computed} from 'vue';
  import {useSubject} from '@/stores/subjects-store.js';
  import {formatDateTime} from '@/common/filters-date.js';

  const props = defineProps({model: Object, noLink: Boolean});
  const i18n = useI18n();

  const {subject} = useSubject(props.model);
  const caption = computed(() => props.model.caption || i18n.t('Direct link') + ' ID' + props.model.id);
  const linkSpec = computed(() => (props.noLink ? {} : {name: 'directLink', params: {id: props.model.id}}));
</script>

<template>
  <square-link class="clearfix pointer lift-up yellow" @click="$emit('click')">
    <router-link :to="{name: 'myOauthApp', params: {id: model.id}}">
      <div class="clearfix">
        <h2 class="pull-left">
          ID<strong>{{ model.id }} </strong>
        </h2>
      </div>
      <dl class="ellipsis">
        <dd>{{ $t('Public ID') }}</dd>
        <dt>{{ ellipsis(model.publicId) }}</dt>
      </dl>
      <div class="separator"></div>
      {{ model.name }}
    </router-link>
  </square-link>
</template>

<script setup>
  import {ellipsis} from '@/common/filters';
  import SquareLink from '@/common/tiles/square-link.vue';

  defineProps({model: Object});
</script>

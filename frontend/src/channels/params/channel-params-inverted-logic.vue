<template>
  <div>
    <dl>
      <dd>{{ $t('Inverted logic') }}</dd>
      <dt class="text-center">
        <toggler v-model="channel.config.invertedLogic" @update:model-value="$emit('change')"></toggler>
      </dt>
    </dl>
  </div>
</template>

<script>
  import Toggler from '../../common/gui/toggler.vue';

  export default {
    components: {Toggler},
    props: {channel: Object},
  };
</script>

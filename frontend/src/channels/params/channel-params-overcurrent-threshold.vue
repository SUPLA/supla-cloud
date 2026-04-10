<template>
  <div>
    <dl v-if="channel.config.overcurrentMaxAllowed">
      <dd>{{ $t('Overcurrent threshold') }}</dd>
      <dt>
        <div class="d-flex align-items-center ml-2">
          <label class="checkbox2 checkbox2-grey">
            <input
              type="checkbox"
              :checked="channel.config.overcurrentThreshold > 0"
              @change="
                channel.config.overcurrentThreshold = channel.config.overcurrentThreshold > 0 ? 0 : channel.config.overcurrentMaxAllowed;
                emit('change');
              "
            />
          </label>
          <NumberInput
            v-if="channel.config.overcurrentThreshold > 0"
            v-model="channel.config.overcurrentThreshold"
            :min="0.01"
            :max="channel.config.overcurrentMaxAllowed"
            suffix=" A"
            :precision="2"
            @update:model-value="emit('change')"
            required
          />
          <input v-else type="text" class="form-control text-center" disabled :placeholder="$t('Disabled')" />
        </div>
      </dt>
    </dl>
  </div>
</template>

<script setup>
  import NumberInput from '@/common/number-input.vue';

  defineProps({channel: Object});

  const emit = defineEmits(['change']);
</script>

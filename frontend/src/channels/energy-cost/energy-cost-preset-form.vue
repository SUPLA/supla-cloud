<script setup>
  import {computed} from 'vue';
  import EnergyCostPresetInput from './energy-cost-preset-input.vue';
  import {inputsForComponent, presetDefault} from './energy-cost-plan-utils';

  const props = defineProps({
    component: {type: Object, required: true},
    componentIndex: {type: Number, required: true},
    preset: {type: Object, required: true},
    errors: {type: Object, required: true},
    idPrefix: {type: String, default: 'energy-cost'},
  });
  const emit = defineEmits(['update:values']);

  const inputs = computed(() => inputsForComponent(props.preset, props.componentIndex));
  const effectiveValue = (input) =>
    Object.hasOwn(props.component.values, input.id) ? props.component.values[input.id] : presetDefault(props.preset, input, props.componentIndex);
  const hasDefault = (input) =>
    presetDefault(props.preset, input, props.componentIndex) !== null && presetDefault(props.preset, input, props.componentIndex) !== undefined;
  const isCustom = (input) => Object.hasOwn(props.component.values, input.id) || !hasDefault(input);
  const override = (input) => emit('update:values', {...props.component.values, [input.id]: effectiveValue(input) ?? ''});
  const reset = (input) => {
    const values = {...props.component.values};
    delete values[input.id];
    emit('update:values', values);
  };
  const update = (input, value) => emit('update:values', {...props.component.values, [input.id]: value});
</script>

<template>
  <div class="energy-cost-preset-form">
    <div v-if="preset.document.warnings?.length" class="alert alert-warning">
      <div v-for="warning in preset.document.warnings" :key="warning">{{ warning }}</div>
    </div>
    <energy-cost-preset-input
      v-for="input in inputs"
      :key="input.id"
      :input="input"
      :id-prefix="idPrefix"
      :value="effectiveValue(input)"
      :timezone="preset.document.timezone"
      :custom="isCustom(input)"
      :has-default="hasDefault(input)"
      :error="errors[input.id]"
      @override="override(input)"
      @reset="reset(input)"
      @update:value="update(input, $event)"
    />
    <details v-if="preset.document.source" class="energy-cost-tariff-details">
      <summary>{{ $t('Tariff details') }}</summary>
      <p v-if="preset.document.source.title">{{ preset.document.source.title }}</p>
      <p v-if="preset.document.source.retrievedAt">{{ $t('Retrieved') }}: {{ preset.document.source.retrievedAt }}</p>
      <a v-if="preset.document.source.url" :href="preset.document.source.url" target="_blank" rel="noopener noreferrer">{{ preset.document.source.url }}</a>
    </details>
  </div>
</template>

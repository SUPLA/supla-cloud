<template>
  <label
    :class="[
      'form-switch',
      {
        disabled: disabled,
      },
    ]"
  >
    <input v-model="theValue" type="checkbox" :disabled="disabled" />
    <span class="check"></span>
    <span class="ml-2">
      <span v-if="label">{{ $t(label) }}</span>
      <slot v-else />
    </span>
  </label>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';

  const props = defineProps({
    label: String,
    disabled: Boolean,
    invert: Boolean,
  });

  const model = defineModel();

  const theValue = computed({
    get: () => (props.invert ? !model.value : model.value),
    set: (value) => (model.value = props.invert ? !value : value),
  });
</script>

<style lang="scss">
  @use '../../styles/variables' as *;

  $switch-width-number: 2.75 !default;
  $switch-width: $switch-width-number * 1em !default;
  $switch-padding: 0.2em !default;
  $speed-slow: 150ms;
  $easing: ease-out;
  $radius: 15px;

  .form-switch {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    position: relative;
    margin-right: 0.5em;

    & + .form-switch:last-child {
      margin-right: 0;
    }

    input[type='checkbox'] {
      position: absolute;
      left: 0;
      opacity: 0;
      outline: none;
      z-index: -1;

      + .check {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        width: $switch-width;
        height: #{$switch-width * 0.5 + $switch-padding};
        padding: $switch-padding;
        background: $supla-grey-dark;
        border-radius: $radius;
        transition:
          background $speed-slow $easing,
          box-shadow $speed-slow $easing;

        &:before {
          content: '';
          display: block;
          border-radius: $radius;
          width: #{($switch-width - $switch-padding * 2) * 0.5};
          height: #{($switch-width - $switch-padding * 2) * 0.5};
          background: $supla-white;
          box-shadow:
            0 3px 1px 0 rgba(0, 0, 0, 0.05),
            0 2px 2px 0 rgba(0, 0, 0, 0.1),
            0 3px 3px 0 rgba(0, 0, 0, 0.05);
          transition: transform $speed-slow $easing;
          will-change: transform;
          transform-origin: left;
        }
      }

      &:checked + .check {
        background: $supla-green;

        &:before {
          transform: translate3d(100%, 0, 0);
        }
      }

      &:focus,
      &:active {
        outline: none;

        + .check {
          box-shadow: 0 0 0.5em $supla-grey-dark;
        }

        &:checked + .check {
          box-shadow: 0 0 0.5em $supla-green;
        }
      }
    }

    &.disabled {
      opacity: 0.5;
      cursor: not-allowed;
      color: $supla-grey-light;
    }
  }
</style>

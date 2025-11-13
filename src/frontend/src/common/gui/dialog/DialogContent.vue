<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {inject, useTemplateRef} from 'vue';
  import {onClickOutside, onKeyStroke} from '@vueuse/core';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';

  const {opened, close, confirm, loading, cancellable, dialogCssClasses} = inject('dialog');

  const dialogContainer = useTemplateRef('dialogContainer');
  onClickOutside(dialogContainer, close);

  onKeyStroke('Escape', () => !loading.value && close());
  onKeyStroke('Enter', () => !loading.value && confirm());
</script>

<template>
  <Teleport to="body">
    <Transition name="dialog">
      <div v-if="opened" class="dialog-mask">
        <div class="dialog-container" ref="dialogContainer" :class="dialogCssClasses">
          <div class="dialog-header">
            <slot name="header">default header</slot>
          </div>
          <div class="dialog-body">
            <slot>default body</slot>
          </div>
          <div class="dialog-footer d-flex align-items-center justify-content-end">
            <slot name="footer">
              <div :class="{invisible: loading}">
                <a v-if="cancellable" class="cancel" @click="close()">
                  <i class="pe-7s-close"></i>
                </a>
                <a class="confirm" @click="confirm()">
                  <i class="pe-7s-check"></i>
                </a>
              </div>
              <button-loading-dots v-if="loading"></button-loading-dots>
            </slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style lang="scss">
  @use '@/styles/variables' as *;
  @use '@/styles/mixins' as *;

  .dialog-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    transition: opacity 0.2s ease;
  }

  .dialog-container {
    width: 90%;
    max-width: 600px;
    margin: auto;
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.2s ease;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    @include on-xs-and-down {
      padding: 5px;
    }
    h4 {
      font-size: 2em;
      color: $supla-green;
    }
    .dialog-header,
    .dialog-footer {
      border: 0;
    }
    .dialog-footer {
      a i {
        vertical-align: middle;
        font-size: 4em;
      }
    }
  }

  @mixin dialog-variant($type, $color) {
    .dialog-container.dialog-#{$type} {
      h4 {
        color: $color;
      }
      .dialog-footer a:not(.btn) {
        color: $color;
      }
    }
  }

  @include dialog-variant(warning, $supla-orange);

  .dialog-body {
    margin: 20px 0;
    overflow-y: auto;
  }

  .dialog-800 {
    .dialog-container {
      max-width: 800px;
    }
  }

  .dialog-450 {
    .dialog-container {
      max-width: 450px;
    }
  }

  .dialog-enter-from {
    opacity: 0;
  }

  .dialog-leave-to {
    opacity: 0;
  }

  .dialog-enter-from .dialog-container,
  .dialog-leave-to .dialog-container {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }

  .dialog-footer {
    a.cancel {
      color: $supla-grey-dark !important;
    }
  }
</style>

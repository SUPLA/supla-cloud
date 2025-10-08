<template>
  <form @submit.prevent="$emit('save')">
    <div class="clearfix left-right-header mb-1">
      <h2 v-if="header && dontSetPageTitle" class="no-margin-top">{{ header }}</h2>
      <h1 v-else-if="header" v-title class="no-margin-top">{{ header }}</h1>
      <h2 v-else class="no-margin-top">&nbsp;</h2>
      <div v-show="!frontendConfig.config.maintenanceMode" class="button-container no-margin-top">
        <Transition name="fade" mode="out-in">
          <div v-if="showPendingButtons">
            <FormButton v-if="cancellable" :disabled="loading" button-class="btn-grey" class="mr-2" @click="$emit('cancel')">
              <i class="pe-7s-back"></i>
              {{ $t('Cancel changes') }}
            </FormButton>
            <FormButton :loading="loading" submit button-class="btn-white">
              <i class="pe-7s-diskette"></i>
              {{ $t('Save changes') }}
            </FormButton>
          </div>
          <div v-else>
            <slot name="buttons"></slot>
            <div v-if="deletable" class="btn-toolbar">
              <a class="btn btn-danger" @click="$emit('delete')">
                {{ $t('Delete') }}
              </a>
            </div>
          </div>
        </Transition>
      </div>
    </div>
    <div class="form-group">
      <slot></slot>
    </div>
    <div class="form-group visible-xs text-center my-3">
      <transition name="fade">
        <div v-if="isPending">
          <FormButton v-if="cancellable" :disabled="loading" button-class="btn-grey" class="mr-3" @click="$emit('cancel')">
            <i class="pe-7s-back"></i>
            {{ $t('Cancel changes') }}
          </FormButton>
          <FormButton :loading="loading" submit button-class="btn-white">
            <i class="pe-7s-diskette"></i>
            {{ $t('Save changes') }}
          </FormButton>
        </div>
      </transition>
    </div>
  </form>
</template>

<script setup>
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {computed} from 'vue';
  import FormButton from '@/common/gui/FormButton.vue';

  const props = defineProps({
    header: String,
    loading: Boolean,
    deletable: Boolean,
    cancellable: {
      type: Boolean,
      default: true,
    },
    isPending: Boolean,
    dontSetPageTitle: Boolean,
  });

  defineEmits(['save', 'cancel', 'delete']);

  const frontendConfig = useFrontendConfigStore();

  const showPendingButtons = computed(() => props.isPending);
</script>

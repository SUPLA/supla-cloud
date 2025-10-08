<template>
  <span class="password-display" @click.stop="">
    <span class="password text-monospace">{{ thePassword }}</span>
    <a
      class="uncover-link password-link"
      @mousedown="uncovered = true"
      @touchstart="uncovered = true"
      @mouseleave="uncovered = false"
      @touchend="uncovered = false"
      @mouseup="uncovered = false"
    >
      <i class="pe-7s-look"></i>
    </a>
    <a v-if="editable" class="password-link" @click="editing = true">
      <i class="pe-7s-note"></i>
    </a>
    <modal
      v-if="editing"
      class="square-modal-chooser"
      cancellable="true"
      :header="$t('Enter new password')"
      @cancel="editing = false"
      @confirm="dispatchNewPassword()"
    >
      <span class="input-group">
        <input v-model="newPassword" type="text" class="form-control" />
        <span class="input-group-btn">
          <a class="btn btn-white" @click="generatePassword(password.length || 5)">{{ $t('GENERATE') }}</a>
        </span>
      </span>
    </modal>
  </span>
</template>

<script>
  import {generatePassword} from '../utils';
  import Modal from '@/common/modal.vue';

  export default {
    components: {Modal},
    props: ['password', 'editable'],
    data() {
      return {
        uncovered: false,
        editing: false,
        newPassword: '',
      };
    },
    computed: {
      thePassword() {
        return this.uncovered ? this.password : this.password.replace(/./g, '*');
      },
    },
    methods: {
      generatePassword(length) {
        this.newPassword = generatePassword(length);
      },
      dispatchNewPassword() {
        if (this.newPassword) {
          this.$emit('change', this.newPassword);
          this.newPassword = '';
        }
        this.editing = false;
      },
    },
  };
</script>

<style lang="scss">
  .password-display {
    .uncover-link {
      display: inline-block;
      margin-left: 5px;
    }
    a.password-link {
      font-size: 1.3em;
      padding: 0 5px;
      vertical-align: middle;
    }
  }
</style>

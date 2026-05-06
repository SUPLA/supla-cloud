<template>
  <span class="password-display" @click.stop="">
    <a
      class="uncover-link password-link"
      @mousedown="uncover()"
      @touchstart="uncover()"
      @mouseleave="uncovered = false"
      @touchend="uncovered = false"
      @mouseup="uncovered = false"
    >
      <i class="pe-7s-look"></i>
    </a>
    <span class="password text-monospace">{{ thePassword }}</span>
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
          <a class="btn btn-white" @click="generatePassword(6)">{{ $t('GENERATE') }}</a>
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
    props: {
      password: String,
      editable: Boolean,
      fetchPassword: Function,
    },
    data() {
      return {
        fetchedPassword: '',
        uncovered: false,
        editing: false,
        newPassword: '',
      };
    },
    computed: {
      thePassword() {
        return this.uncovered ? this.password || this.fetchedPassword : '******';
      },
    },
    methods: {
      async uncover() {
        if (!this.fetchedPassword && this.fetchPassword) {
          this.fetchedPassword = await this.fetchPassword();
        }
        this.uncovered = true;
      },
      generatePassword(length) {
        this.newPassword = generatePassword(length);
      },
      dispatchNewPassword() {
        if (this.newPassword) {
          this.$emit('change', this.newPassword);
          this.newPassword = '';
          this.fetchedPassword = '';
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

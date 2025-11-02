<template>
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-lg-offset-3">
        <div class="alert alert-info">
          <p>
            {{
              $t(
                'If you need help that requires some other person to authenticate into your account, you can generate a temporary additional password for your account.'
              )
            }}
          </p>
          <p>
            {{
              $t(
                'Keep in mind that the password you generate here gives almost full access to your account. Make sure you trust person who receives it from you.'
              )
            }}
          </p>
        </div>

        <div v-if="technicalPasswordValidTo">
          <div v-if="technicalPassword" class="alert alert-success">
            <div class="form-group">
              {{ $t('The support access password has been generated.') }}
              <strong>{{ $t('Make sure to copy it now because you won’t be able to see it again!') }}</strong>
            </div>
            <pre><code>{{ technicalPassword }}</code></pre>
            <copy-button :text="technicalPassword"></copy-button>
          </div>
          <div class="alert-warning alert">
            {{ $t('Support access is enabled and valid to:') }}
            {{ formatDateTime(technicalPasswordValidTo) }}
          </div>
          <FormButton :loading="loading" button-class="btn-danger" @click="disableSupportAccess()">
            {{ $t('Turn off support access') }}
          </FormButton>
        </div>

        <form @submit.prevent="enableSupportAccess()" v-else>
          <div class="form-group">
            <input id="old-password" v-model="accountPassword" type="password" class="form-control" />
            <label for="old-password">{{ $t('Your account Password') }}</label>
          </div>
          <FormButton submit :loading="loading" button-class="btn-green">
            {{ $t('Enable support access') }}
          </FormButton>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
  import FormButton from '@/common/gui/FormButton.vue';
  import {usersApi} from '@/api/users-api.js';
  import {onMounted, ref} from 'vue';
  import {formatDateTime} from '@/common/filters-date.js';
  import CopyButton from '@/common/copy-button.vue';

  const loading = ref(false);
  const accountPassword = ref('');
  const technicalPassword = ref('');
  const technicalPasswordValidTo = ref(undefined);

  onMounted(async () => {
    const {body} = await usersApi.getCurrent();
    technicalPasswordValidTo.value = body.technicalPasswordValidTo;
  });

  async function enableSupportAccess() {
    loading.value = true;
    try {
      const {body, headers} = await usersApi.technicalPasswordEnable(accountPassword.value);
      technicalPassword.value = headers.get('SUPLA-Technical-Password');
      technicalPasswordValidTo.value = body.technicalPasswordValidTo;
      accountPassword.value = '';
    } finally {
      loading.value = false;
    }
  }

  async function disableSupportAccess() {
    loading.value = true;
    try {
      const {body} = await usersApi.technicalPasswordDisable();
      technicalPasswordValidTo.value = body.technicalPasswordValidTo;
    } finally {
      loading.value = false;
    }
  }
</script>

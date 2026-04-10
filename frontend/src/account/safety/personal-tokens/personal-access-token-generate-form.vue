<template>
  <form @submit.prevent="saveNewToken()">
    <div class="form-group">
      <label>{{ $t('Name') }}</label>
      <input v-model="token.name" type="text" class="form-control" :placeholder="$t('What this token is for?')" />
    </div>
    <div class="form-group clearfix">
      <label>{{ $t('Scopes') }}</label>
      <p>{{ $t('Scopes define which sections of your account can be accessed when using the token.') }}</p>
      <div class="list-group scope-selector">
        <div v-for="scope in availableScopes" :key="scope.label" class="list-group-item">
          <h4>{{ $t(scope.label) }}</h4>
          <div class="togglers">
            <div v-for="suffix in scope.suffixes" :key="suffix">
              <toggler
                v-model="selectedScopes[scopeId(scope, suffix)]"
                :label="scopeSuffixLabels[suffix]"
                @update:model-value="scopeChanged(scope, suffix, $event)"
              ></toggler>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-right">
      <button class="btn btn-green" type="submit">
        {{ $t('Generate token') }}
      </button>
      <button class="btn btn-white" type="button" @click="$emit('cancel')">
        {{ $t('Cancel') }}
      </button>
    </div>
  </form>
</template>

<script>
  import {availableScopes, scopeId, scopeSuffixLabels} from '../../integrations/oauth-scopes';
  import Toggler from '@/common/gui/toggler.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {Toggler},
    data() {
      return {
        availableScopes: availableScopes.filter((scope) => !['offline', 'mqtt', 'state'].includes(scope.prefix)),
        scopeSuffixLabels,
        selectedScopes: {},
        token: {
          name: '',
          scope: [],
        },
      };
    },
    mounted() {},
    methods: {
      saveNewToken() {
        this.token.scope = [];
        for (let scope in this.selectedScopes) {
          if (this.selectedScopes[scope]) {
            this.token.scope.push(scope);
          }
        }
        api.post('oauth-personal-tokens', this.token).then((response) => {
          this.$emit('generated', response.body);
        });
      },
      scopeChanged(scope, suffix, set) {
        if (suffix == 'r' && !set) {
          this.selectedScopes[this.scopeId(scope, 'rw')] = false;
        } else if (suffix == 'rw' && set) {
          this.selectedScopes[this.scopeId(scope, 'r')] = true;
        }
      },
      scopeId,
    },
  };
</script>

<style lang="scss">
  .scope-selector {
    display: flex;
    flex-flow: row wrap;
    > div {
      flex-grow: 1;
      border-radius: 0 !important;
      h4 {
        margin-top: 0;
      }
      .togglers {
        display: flex;
        justify-content: space-evenly;
        > div {
          padding: 0 3px;
          &:first-child {
            padding-left: 0;
          }
          &:last-child {
            padding-right: 0;
          }
        }
      }
    }
  }
</style>

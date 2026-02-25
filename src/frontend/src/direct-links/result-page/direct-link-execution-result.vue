<template>
  <div v-if="directLink && directLink.id === linkId" class="container text-center">
    <div v-if="failureReason">
      <span v-title class="hidden">{{ $t('Error') }}</span>
      <h1 class="nocapitalize">{{ $t(failureReason) }}</h1>
      <div v-if="failureReason == 'directLinkExecutionFailureReason_invalidActionParameters'">
        <i class="pe-7s-edit" style="font-size: 160px"></i>
        <p>
          {{ $t('directLinkExecutionFailureReason_invalidActionParameters_info') }}
        </p>
      </div>
      <div v-else-if="failureReason == 'directLinkExecutionFailureReason_invalidChannelState'">
        <i class="pe-7s-science" style="font-size: 160px"></i>
        <div v-if="['VALVEOPENCLOSE'].indexOf(directLink.subject.function.name) !== -1">
          <p>
            {{ $t('The valve cannot be opened via a direct link or via API once it has been closed manually. To resume control, open the valve manually.') }}
          </p>
        </div>
      </div>
      <div v-else>
        <i class="pe-7s-close-circle" style="font-size: 160px"></i>
        <h5>{{ $t('Check the direct link you used and try again.') }}</h5>
      </div>
    </div>
    <div v-else class="form-group">
      <div v-if="!action">
        <h1 v-title>{{ directLink.caption }}</h1>
        <direct-link-channel-status
          v-if="directLink.allowedActions.filter((f) => f.name === 'READ').length"
          :direct-link="directLink"
        ></direct-link-channel-status>
        <div style="max-width: 850px; margin: 0 auto" class="text-left">
          <div v-for="allowedAction in directLink.allowedActions" :key="allowedAction" class="form-group">
            <div class="flex-left-full-width">
              <span class="label label-default hidden-xs">{{ actionCaption(allowedAction, directLink.subject) }}</span>
              <pre><code>{{ exampleUrl(allowedAction) }}</code></pre>
              <div class="btn-group">
                <copy-button :text="exampleUrl(allowedAction)"></copy-button>
                <button
                  v-if="allowedAction.name !== 'READ' && !ChannelFunctionAction.requiresParams(allowedAction.id)"
                  :class="'btn btn-' + (allowedAction.executed ? 'success' : 'default')"
                  :disabled="allowedAction.executing"
                  type="button"
                  @click="executeAction(allowedAction)"
                >
                  <span>
                    <i :class="'pe-7s-' + (allowedAction.executed ? 'check' : 'rocket')"></i>
                    {{ allowedAction.executed ? $t('executed') : actionCaption(allowedAction, directLink.subject) }}
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="directLink.state">
        <direct-link-channel-status :direct-link="directLink"></direct-link-channel-status>
      </div>
      <div v-else>
        <h1 class="nocapitalize">{{ $t('Direct link has been executed') }}</h1>
        <i class="pe-7s-rocket" style="font-size: 160px"></i>
      </div>
    </div>
    <button v-if="action && !jsonHintVisible" type="button" class="btn btn-link" @click="jsonHintVisible = true">
      <i class="pe-7s-help1"></i>
    </button>
    <div v-if="jsonHintVisible" class="well mt-4" style="max-width: 600px; margin: 0 auto">
      <h3 class="no-margin-top">{{ $t('Where is the JSON?') }}</h3>
      <p>{{ $t('The link result is presented as a website response, because you (or your browser) asked for HTML output.') }}</p>
      <p>{{ $t('If you want to use the link programmatically, add an appropriate request header to obtain an output you can easily parse.') }}</p>
      <pre><code>Accept: application/json</code></pre>
      <p>{{ $t('Alternatively, you can add a GET parameter to specify response format.') }}</p>
      <pre><code>{{ currentUrl }}?format=json</code></pre>
    </div>
  </div>
  <Error404 v-else />
</template>

<script>
  import CopyButton from '../../common/copy-button.vue';
  import DirectLinkChannelStatus from './direct-link-channel-status.vue';
  import ChannelFunction from '@/common/enums/channel-function';
  import ChannelFunctionAction from '@/common/enums/channel-function-action';
  import {actionCaption} from '../../channels/channel-helpers';
  import Error404 from '@/common/errors/error-404.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {Error404, DirectLinkChannelStatus, CopyButton},
    props: {action: String, linkId: Number},
    data() {
      return {
        directLink: undefined,
        jsonHintVisible: false,
        failureReason: undefined,
      };
    },
    computed: {
      ChannelFunctionAction() {
        return ChannelFunctionAction;
      },
      currentUrl() {
        return window.location.protocol + '//' + window.location.host + window.location.pathname;
      },
    },
    mounted() {
      this.directLink = window.directLink;
      this.failureReason = window.failureReason;
    },
    methods: {
      actionCaption,
      executeAction(action) {
        this.$set(action, 'executing', true);
        api
          .get(this.currentUrl + '/' + action.nameSlug)
          .then(() => {
            this.$set(action, 'executed', true);
            setTimeout(() => this.$set(action, 'executed', false), 3000);
          })
          .finally(() => this.$set(action, 'executing', false));
      },
      exampleUrl(action) {
        let url = this.currentUrl + '/' + action.nameSlug;
        if (action.nameSlug === 'set-rgbw-parameters') {
          if (this.directLink.subject.functionId === ChannelFunction.RGBLIGHTING) {
            url += '?color_brightness=40&color=0x00FF33';
          } else if (this.directLink.subject.functionId === ChannelFunction.DIMMERANDRGBLIGHTING) {
            url += '?color_brightness=40&color=0x00FF33&brightness=60';
          } else if (this.directLink.subject.functionId === ChannelFunction.DIMMER_CCT_AND_RGB) {
            url += '?color_brightness=40&color=0x00FF33&brightness=60&white_temperature=33';
          } else if (this.directLink.subject.functionId === ChannelFunction.DIMMER_CCT) {
            url += '?brightness=60&white_temperature=33';
          } else {
            url += '?brightness=60';
          }
        } else if (action.nameSlug === 'reveal-partially') {
          url += '?percentage=60';
        } else if (action.nameSlug === 'set') {
          if (this.directLink.subject.function.name.match(/^DIGIGLASS.+/)) {
            url += '?transparent=0,2&opaque=1';
          }
        } else if (action.nameSlug === 'copy') {
          url += '?sourceChannelId=123';
        }
        return url;
      },
    },
  };
</script>

<template>
  <div v-if="directLink.state" class="form-group">
    <h3 class="nocapitalize">{{ subjectCaption }}</h3>
    <div>
      <div v-for="channel in channels" :key="channel.id" style="display: inline-block">
        <div class="form-group">
          <function-icon
            :model="{...channel, state: channelsState[channel.id]}"
            :title="channel.caption"
            :user-icon="channel.userIcon"
            width="100"
          ></function-icon>
          <channel-state-table :state="channelsState[channel.id]" :channel="channel"></channel-state-table>
        </div>
      </div>
    </div>
    <button v-if="readStateUrl" type="button" :disabled="refreshingState" class="btn btn-xs btn-default" @click="refreshState()">
      <i class="pe-7s-refresh-2"></i>
      {{ $t('Refresh') }}
    </button>
  </div>
</template>

<script>
  import FunctionIcon from '../../channels/function-icon.vue';
  import ChannelStateTable from '../../channels/channel-state-table.vue';
  import {channelTitle} from '../../common/filters';
  import {api} from '@/api/api.js';

  export default {
    components: {ChannelStateTable, FunctionIcon},
    props: ['directLink'],
    data() {
      return {
        refreshingState: false,
      };
    },
    computed: {
      currentUrl() {
        return window.location.protocol + '//' + window.location.host + window.location.pathname;
      },
      readStateUrl() {
        if (this.currentUrl.indexOf('/read') > 0) {
          return this.currentUrl;
        }
        const match = this.currentUrl.match(/.+\/direct\/[0-9]+\/.+\//);
        if (match) {
          return match[0] + 'read';
        }
        return undefined;
      },
      subjectCaption() {
        return channelTitle(this.directLink.subject).replace(/^ID[0-9]+/, '');
      },
      availableChannelsIds() {
        return this.directLink.state ? Object.keys(this.directLink.state) : [];
      },
      channels() {
        if (this.directLink.subject.ownSubjectType === 'channelGroup') {
          return this.directLink.channels;
        } else {
          return [this.directLink.subject];
        }
      },
      channelsState() {
        if (this.directLink.subject.ownSubjectType === 'channelGroup') {
          return this.directLink.state;
        } else {
          return {[this.directLink.subject.id]: this.directLink.state};
        }
      },
    },
    mounted() {
      if (!this.directLink.state) {
        this.refreshState();
      }
    },
    methods: {
      refreshState() {
        this.refreshingState = true;
        api
          .get(this.readStateUrl)
          .then((response) => (this.directLink.state = response.body))
          .finally(() => (this.refreshingState = false));
      },
    },
  };
</script>

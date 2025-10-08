<template>
  <span v-if="functionId !== undefined" class="channel-icon">
    <template v-if="model.userIconId || userIcon">
      <img :src="userIconSrc" :style="{maxWidth: imageWidth + 'px', maxHeight: imageWidth + 'px'}" />
      <!-- Double icon display for HUMIDITYANDTEMPERATURE function. -->
      <img v-if="functionId === 45" :src="userIconSrcForState(1)" :style="{maxWidth: imageWidth + 'px', maxHeight: imageWidth + 'px'}" />
    </template>
    <template v-else>
      <img
        v-if="functionId === 45"
        :src="withBaseUrl('/assets/img/functions/' + functionId + alternativeSuffix + '-hum.svg')"
        :width="imageWidth"
        :height="imageWidth"
      />
      <img :src="withBaseUrl('/assets/img/functions/' + functionId + alternativeSuffix + stateSuffix + '.svg')" :width="imageWidth" :height="imageWidth" />
    </template>
  </span>
</template>

<script>
  import {withBaseUrl, withDownloadAccessToken} from '../common/filters';
  import ChannelFunction from '@/common/enums/channel-function';

  export default {
    props: ['model', 'width', 'alternative', 'userIcon', 'config', 'flexibleWidth'],
    computed: {
      functionId() {
        if (this.model) {
          if (this.model.function) {
            return this.model.function.name === 'UNSUPPORTED' ? 0 : this.model.function.id;
          } else if (this.model.functionId) {
            return this.model.functionId;
          } else if (this.model.id) {
            return this.model.id;
          } else if (Number.isInteger(this.model)) {
            return this.model;
          }
        }
        return 0;
      },
      alternativeSuffix() {
        if (this.alternative !== undefined) {
          return this.alternative ? `_${this.alternative}` : '';
        }
        if (this.model) {
          if (this.model.altIcon > 0) {
            return `_${this.model.altIcon}`;
          }
        }
        return '';
      },
      stateSuffix() {
        const state = this.model.state;
        if (state) {
          if (state.hi || state.shut > 99 || state.closed === true || state.closed > 99) {
            return '-closed';
          }
          if (state.partial_hi) {
            return '-partial';
          }
          if (state.color_brightness !== undefined && state.brightness !== undefined) {
            return '-' + (state.brightness ? 'on' : 'off') + (state.color_brightness ? 'on' : 'off');
          } else if (state.color_brightness > 0 || state.brightness > 0) {
            return '-on';
          }
          if (state.on === true) {
            return '-on';
          }
          if (state.transparent && state.transparent.length > 0) {
            return '-transparent';
          }
          if (state.fillLevel > 20) {
            return state.fillLevel > 80 ? '-full' : '-half';
          }
        }
        const config = this.model.config || this.config || {};
        if (config.subfunction) {
          if (config.subfunction === 'COOL') {
            return '-cool';
          }
        }
        return '';
      },
      stateIndex() {
        const suffix = this.stateSuffix;
        if (['-closed', '-on', '-onoff', '-cool'].indexOf(suffix) !== -1) {
          return 1;
        } else if (['-offon', '-partial'].indexOf(suffix) !== -1) {
          return 2;
        } else if ('-onon' === suffix) {
          return 3;
        } else {
          return 0;
        }
      },
      userIconSrc() {
        return this.userIconSrcForState(this.stateIndex);
      },
      imageWidth() {
        if (this.functionId === ChannelFunction.HUMIDITYANDTEMPERATURE && !this.flexibleWidth) {
          return this.width / 2;
        } else {
          return this.width;
        }
      },
    },
    methods: {
      withBaseUrl,
      userIconSrcForState(stateIndex) {
        if (this.userIcon) {
          return 'data:image/png;base64,' + this.userIcon.images[stateIndex];
        } else {
          return withDownloadAccessToken(`/api/user-icons/${this.model.userIconId}/${stateIndex}?`);
        }
      },
    },
  };
</script>

<style lang="scss">
  .channel-icon {
    line-height: 0;
    img {
      max-width: 100%;
    }
  }
</style>

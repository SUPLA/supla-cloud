<template>
  <div>
    <div class="text-center">
      <div class="digiglass-parameters-setter-global-btns btn-group">
        <a class="btn btn-link" @click="setAll(true)">
          <img :src="withBaseUrl('/assets/img/digiglass/transparent.png')" />
        </a>
        <a class="btn btn-link" @click="setAll(false)">
          <img :src="withBaseUrl('/assets/img/digiglass/opaque.png')" />
        </a>
      </div>
    </div>
    <div :class="'mb-3 digiglass-parameters-setter digiglass-' + (subject.function.name.indexOf('HORIZONTAL') > 0 ? 'horizontal' : 'vertical')">
      <function-icon class="digiglass-bg" :model="subject" width="200"></function-icon>
      <!--            <img class="digiglass-bg"-->
      <!--                :src="'/assets/img/digiglass/window.svg' | withBaseUrl">-->
      <div class="digiglass-sections">
        <div
          v-for="(e, sectionNumber) in numberOfSections"
          :key="sectionNumber"
          :class="['digiglass-section', {'digiglass-section-transparent': state[sectionNumber], 'digiglass-section-opaque': state[sectionNumber] === false}]"
        >
          <div class="digiglass-glass" @click="toggleSection(sectionNumber)"></div>
          <div class="digiglass-icon" @click="toggleSection(sectionNumber)">
            <img :src="withBaseUrl('/assets/img/digiglass/transparent.png')" class="transparent-image" />
            <img :src="withBaseUrl('/assets/img/digiglass/opaque.png')" class="opaque-image" />
          </div>
          <label class="checkbox2 small">
            <input v-model="activeSections[sectionNumber]" type="checkbox" @click.stop="enableDisableSection(sectionNumber)" />
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import FunctionIcon from '../function-icon.vue';
  import {withBaseUrl} from '@/common/filters.js';

  export default {
    components: {FunctionIcon},
    props: ['subject', 'value'],
    data() {
      return {
        state: [...Array(7)],
      };
    },
    computed: {
      numberOfSections() {
        return +this.subject.config.sectionsCount || 7;
      },
      activeSections() {
        return this.state.map((v) => v !== undefined);
      },
      channelState() {
        const state = {transparent: [], opaque: []};
        for (let i = 0; i < this.numberOfSections; i++) {
          if (this.state[i] !== undefined) {
            state[this.state[i] ? 'transparent' : 'opaque'].push(i);
          }
        }
        return state;
      },
    },
    mounted() {
      this.updateState();
    },
    methods: {
      withBaseUrl,
      updateState() {
        if (this.value) {
          if (this.value.transparent) {
            this.value.transparent.forEach((index) => (this.state[index] = true));
          }
          if (this.value.opaque) {
            this.value.opaque.forEach((index) => (this.state[index] = false));
          }
          this.state = [...this.state];
        }
      },
      toggleSection(sectionIndex) {
        this.state.splice(sectionIndex, 1, !this.state[sectionIndex]);
        this.onChange();
      },
      enableDisableSection(sectionIndex) {
        if (this.state[sectionIndex] !== undefined) {
          this.state.splice(sectionIndex, 1, undefined);
          this.onChange();
        } else {
          this.toggleSection(sectionIndex);
        }
      },
      setAll(transparent) {
        if (transparent) {
          this.state = [...Array(7)].map(() => true);
        } else {
          this.state = [...Array(7)].map(() => false);
        }
        this.onChange();
      },
      onChange() {
        this.$emit('input', this.channelState);
      },
    },
  };
</script>

<style lang="scss">
  .digiglass-parameters-setter {
    $sizeSmaller: 200px;
    $sizeBigger: $sizeSmaller + 60px;
    position: relative;
    margin: 0 auto;
    .digiglass-bg {
      left: 0;
      top: 0;
      position: absolute;
      height: $sizeSmaller;
      width: $sizeSmaller;
      z-index: 1;
    }
    .digiglass-sections {
      position: relative;
      width: 100%;
      height: 100%;
      display: flex;
      z-index: 2;
      .digiglass-section {
        cursor: pointer;
        background: rgba(#fff, 0.5);
        flex-grow: 1;
        display: flex;
        align-items: center;
        img {
          height: 100%;
          display: none;
        }
        .digiglass-glass {
          transition: 0.2s linear background-color;
        }
        .digiglass-icon {
          width: 20px;
          height: 20px;
        }
        &:not(.digiglass-section-opaque):not(.digiglass-section-transparent):hover {
          background: rgba(#ccc, 0.5);
        }
        &.digiglass-section-opaque,
        &.digiglass-section-transparent {
          .digiglass-glass {
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABZJREFUeNpi2r9//38gYGAEESAAEGAAasgJOgzOKCoAAAAASUVORK5CYII=);
          }
        }
        &.digiglass-section-opaque {
          .digiglass-glass {
            background-color: rgba(#4eb8e6, 0.9);
          }
          &:hover {
            .digiglass-glass {
              background-color: rgba(#4eb8e6, 0.99);
            }
          }
          img.opaque-image {
            display: inline-block;
          }
        }
        &.digiglass-section-transparent {
          .digiglass-glass {
            background-color: rgba(#4eb8e6, 0.2);
          }
          &:hover {
            .digiglass-glass {
              background-color: rgba(#4eb8e6, 0.4);
            }
          }
          img.transparent-image {
            display: inline-block;
          }
        }
      }
    }
    &.digiglass-horizontal {
      height: $sizeSmaller;
      width: $sizeBigger;
      .digiglass-sections {
        flex-direction: column;
      }
      .digiglass-section {
        flex-direction: row;
        .digiglass-icon {
          margin: 0 10px;
        }
        .digiglass-glass {
          height: 100%;
          width: $sizeSmaller;
        }
      }
    }
    &.digiglass-vertical {
      height: $sizeBigger;
      width: $sizeSmaller;
      .digiglass-section {
        flex-direction: column;
        .digiglass-icon {
          margin: 10px 0;
        }
        .digiglass-glass {
          width: 100%;
          height: $sizeSmaller;
        }
      }
    }
  }

  .digiglass-parameters-setter-global-btns {
    img {
      width: 50px;
    }
  }
</style>

<template>
  <div class="user-icon-preview" @mouseenter="startPreview()" @mouseleave="stopPreview()">
    <a v-if="previewTimeout" class="btn btn-yellow btn-xs icon-edit-button" @click="editIcon($event)"><i class="pe-7s-note"></i> </a>
    <img
      v-for="(state, stateIndex) in icon.function.possibleVisualStates"
      v-show="stateIndex == shownIndex"
      :key="stateIndex"
      :src="withDownloadAccessToken(`/api/user-icons/${icon.id}/${stateIndex}?`)"
    />
  </div>
</template>

<script>
  import {withDownloadAccessToken} from '@/common/filters.js';

  export default {
    props: ['icon'],
    data() {
      return {
        shownIndex: 0,
        previewTimeout: undefined,
      };
    },
    methods: {
      withDownloadAccessToken,
      startPreview() {
        this.previewTimeout = setTimeout(() => {
          this.shownIndex++;
          if (this.shownIndex >= this.icon.function.possibleVisualStates.length) {
            this.shownIndex = 0;
          }
          this.startPreview();
        }, 1000);
      },
      stopPreview() {
        clearTimeout(this.previewTimeout);
        this.previewTimeout = undefined;
        this.shownIndex = 0;
      },
      editIcon(event) {
        event.stopPropagation();
        this.icon.edit();
      },
    },
  };
</script>

<style lang="scss">
  .user-icon-preview {
    img {
      max-width: 100%;
      max-height: 100px;
    }
    .icon-edit-button {
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      transition: opacity 0.2s;
      i {
        font-size: 2em;
        margin: 0;
      }
    }
    &:hover {
      .icon-edit-button {
        opacity: 1;
      }
    }
  }
</style>

<template>
  <loading-cover :loading="!cropperReady">
    <div :class="{invisible: !cropperReady}">
      <!--            <img src="https://ocdn.eu/images/pulscms/M2M7MDA_/bd8b7be0ad21e358dcc1d43b5a90672e.jpg" ref="image"/>-->
      <img ref="image" :src="imageSrc" />
    </div>
    <transition-expand>
      <div v-if="editable" :class="[{invisible: !cropperReady}, 'd-flex mt-2']">
        <div class="flex-grow-1">
          <button type="button" class="btn btn-default mr-2" @click="rotate(-90)">
            <fa :icon="faRotateLeft()" />
            90&deg;
          </button>
          <button type="button" class="btn btn-default" @click="rotate(-1)">
            <fa :icon="faRotateLeft()" />
          </button>
        </div>
        <div>
          <button type="button" class="btn btn-default mr-2" @click="rotate(1)">
            <fa :icon="faRotateRight()" />
          </button>
          <button type="button" class="btn btn-default" @click="rotate(90)">
            <fa :icon="faRotateRight()" />
            90&deg;
          </button>
        </div>
      </div>
    </transition-expand>
  </loading-cover>
</template>

<script>
  import 'cropperjs/dist/cropper.css';
  import Cropper from 'cropperjs';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {faRotateLeft, faRotateRight} from '@fortawesome/free-solid-svg-icons';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';

  export default {
    components: {LoadingCover, TransitionExpand},
    props: {
      editable: Boolean,
      value: Object,
      imageBase64: String,
    },
    data() {
      return {
        currentData: undefined,
        cropper: undefined,
        cropperReady: false,
        errorDetails: undefined,
      };
    },
    computed: {
      imageSrc() {
        return 'data:image/jpg;base64,' + this.imageBase64;
      },
    },
    watch: {
      value(newValue) {
        if (newValue !== this.currentData) {
          this.recreateCropper();
        }
      },
      editable() {
        if (this.editable) {
          this.cropper.enable();
        } else {
          this.cropper.disable();
        }
      },
    },
    mounted() {
      this.recreateCropper();
    },
    methods: {
      faRotateRight() {
        return faRotateRight;
      },
      faRotateLeft() {
        return faRotateLeft;
      },
      recreateCropper() {
        this.cropperReady = false;
        this.cropper?.destroy();
        this.cropper = new Cropper(this.$refs.image, {
          guides: false,
          center: false,
          scalable: false,
          autoCropArea: 0.5,
          dragMode: 'move',
          toggleDragModeOnDblclick: false,
          data: this.value ? this.value.crop : null,
          ready: () => {
            if (this.value) {
              this.cropper.setCropBoxData(this.value.cropBox).setCanvasData(this.value.canvas);
            }
            if (!this.editable) {
              this.cropper.disable();
            }
            this.$nextTick(() => (this.cropperReady = true));
          },
          crop: () => {
            if (this.cropperReady) {
              this.currentData = {
                crop: this.cropper.getData(true),
                cropBox: this.cropper.getCropBoxData(),
                canvas: this.cropper.getCanvasData(),
              };
              this.$emit('input', this.currentData);
            }
          },
        });
      },
      rotate(angle) {
        this.cropper.rotate(angle);
      },
    },
  };
</script>

<style lang="scss" scoped>
  img {
    display: block;
    max-width: 100%;
  }

  :deep(.cropper-disabled) {
    .cropper-point {
      display: none;
    }
    .cropper-crop-box,
    .cropper-view-box {
      outline: none;
    }
  }
</style>

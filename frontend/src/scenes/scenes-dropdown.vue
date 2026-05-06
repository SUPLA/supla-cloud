<template>
  <div>
    <SelectForSubjects
      v-model="chosenScene"
      class="scenes-dropdown"
      :options="scenesForDropdown"
      :caption="sceneCaption"
      :search-text="sceneSearchText"
      :option-html="sceneHtml"
      choose-prompt-i18n="choose the scene"
    />
  </div>
</template>

<script>
  import {channelIconUrl} from '../common/filters';
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {SelectForSubjects},
    props: ['params', 'value', 'filter'],
    data() {
      return {
        scenes: undefined,
      };
    },
    computed: {
      scenesForDropdown() {
        if (!this.scenes) {
          return [];
        }
        this.$emit('update', this.scenes);
        return this.scenes;
      },
      chosenScene: {
        get() {
          return this.value;
        },
        set(scene) {
          this.$emit('input', scene);
        },
      },
    },
    watch: {
      params() {
        this.fetchScenes();
      },
    },
    mounted() {
      this.fetchScenes();
    },
    methods: {
      fetchScenes() {
        this.scenes = undefined;
        api.get('scenes' + (this.params || '')).then(({body: scenes}) => {
          this.scenes = scenes.filter(this.filter || (() => true));
        });
      },
      sceneCaption(scene) {
        return scene.caption || `ID${scene.id}`;
      },
      sceneSearchText(scene) {
        return `${scene.caption || ''} ID${scene.id}`;
      },
      sceneHtml(scene, escape) {
        return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(scene.fullCaption)}</span>
                                        ${scene.caption ? `<span class="small text-muted">ID${scene.id}</span>` : ''}
                                    </h5>
                                    <p class="line-clamp line-clamp-2 small mb-0 option-extra">${this.$t('No of operations')}: ${scene.relationsCount.operations}</p>
                                </div>
                                <div class="icon option-extra"><img src="${channelIconUrl(scene)}"></div></div>
                            </div>
                        </div>`;
      },
    },
  };
</script>

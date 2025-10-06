<template>
    <div>
        <SelectForSubjects
            class="scenes-dropdown"
            :options="scenesForDropdown"
            :caption="sceneCaption"
            :search-text="sceneSearchText"
            :option-html="sceneHtml"
            choose-prompt-i18n="choose the scene"
            v-model="chosenScene"/>
    </div>
</template>

<script>
    import {channelIconUrl} from "../common/filters";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        props: ['params', 'value', 'filter'],
        components: {SelectForSubjects},
        data() {
            return {
                scenes: undefined,
            };
        },
        mounted() {
            this.fetchScenes();
        },
        methods: {
            fetchScenes() {
                this.scenes = undefined;
                this.$http.get('scenes' + (this.params || '')).then(({body: scenes}) => {
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
                }
            }
        },
        watch: {
            params() {
                this.fetchScenes();
            },
        }
    };
</script>

<template>
    <div>
        <div class="select-loader"
            v-if="!scenes">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="selectpicker"
            :disabled="!scenes"
            ref="dropdown"
            data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            data-width="100%"
            :data-none-selected-text="$t('choose the scene')"
            :data-none-results-text="$t('No results match {0}')"
            v-model="chosenScene"
            @change="$emit('input', chosenScene)">
            <option v-for="scene in scenesForDropdown"
                :key="scene.id"
                :value="scene"
                :data-content="sceneHtml(scene)">
                {{ sceneCaption(scene) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelIconUrl} from "../common/filters";
    import $ from "jquery";

    export default {
        props: ['params', 'value', 'filter'],
        components: {ButtonLoadingDots},
        data() {
            return {
                scenes: undefined,
                chosenScene: undefined
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
                    this.setSceneFromModel();
                    Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
                });
            },
            sceneCaption(scene) {
                return scene.caption || `ID${scene.id}`;
            },
            sceneHtml(scene) {
                let content = `<div class='channel-dropdown-option flex-left-full-width'>`
                    + `<div class="labels full"><h4>${this.sceneCaption(scene)}`;
                if (scene.caption) {
                    content += ` <span class='small text-muted'>ID${scene.id}</span>`;
                }
                content += '</h4>';
                content += `<p>${this.$t('No of operations')}: ${scene.relationsCount.operations}</p></div>`;
                content += `<div class="icon"><img src="${channelIconUrl(scene)}"></div></div>`;
                content += '</div>';
                return content;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setSceneFromModel() {
                if (this.value && this.scenes) {
                    this.chosenScene = this.scenes.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenScene = undefined;
                }
            }
        },
        computed: {
            scenesForDropdown() {
                this.updateDropdownOptions();
                if (!this.scenes) {
                    return [];
                }
                this.$emit('update', this.scenes);
                return this.scenes;
            }
        },
        watch: {
            value() {
                this.setSceneFromModel();
                this.updateDropdownOptions();
            },
            params() {
                this.fetchScenes();
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .select-loader {
        position: relative;
        text-align: center;
        .button-loading-dots {
            position: absolute;
            top: 8px;
            left: 50%;
            margin-left: -25px;
            z-index: 20;
        }
    }

    .channel-dropdown-option {
        .icon {
            img {
                height: 60px;
            }
        }
    }
</style>

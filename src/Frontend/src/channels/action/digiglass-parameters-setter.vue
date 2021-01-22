<template>
    <div>
        <div class="digiglass-parameters-setter-global-btns btn-group">
            <a class="btn btn-link"
                @click="setAll(true)">
                <img :src="'/assets/img/smartglass/transparent.png' | withBaseUrl">
            </a>
            <a class="btn btn-link"
                @click="setAll(false)">
                <img :src="'/assets/img/smartglass/opaque.png' | withBaseUrl">
            </a>
        </div>
        <div :class="'digiglass-parameters-setter digiglass-' + (subject.function.name.indexOf('HORIZONTAL') > 0 ? 'horizontal' : 'vertical')">
            <img class="digiglass-bg"
                :src="'/assets/img/smartglass/window.svg' | withBaseUrl">
            <div class="digiglass-sections">
                <div :class="['digiglass-section', {'digiglass-section-transparent': state[sectionNumber], 'digiglass-section-opaque': state[sectionNumber] === false}]"
                    v-for="sectionNumber in 7"
                    :key="sectionNumber">
                    <div class="checkbox checkbox-green small">
                        <label>
                            <input type="checkbox"
                                v-model="activeSections[sectionNumber]"
                                @click.stop="enableDisableSection(sectionNumber)">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                    <img :src="'/assets/img/smartglass/transparent.png' | withBaseUrl"
                        class="transparent-image"
                        @click="toggleSection(sectionNumber)">
                    <img :src="'/assets/img/smartglass/opaque.png' | withBaseUrl"
                        class="opaque-image"
                        @click="toggleSection(sectionNumber)">
                    <div class="digiglass-glass"
                        @click="toggleSection(sectionNumber)"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['subject', 'value'],
        data() {
            return {
                state: Array(7),
            };
        },
        mounted() {

        },
        methods: {
            toggleSection(sectionIndex) {
                this.state.splice(sectionIndex, 1, !this.state[sectionIndex]);
            },
            enableDisableSection(sectionIndex) {
                if (this.state !== undefined) {
                    this.state.splice(sectionIndex, 1, undefined);
                } else {
                    this.toggleSection(sectionIndex);
                }
            },
            setAll(transparent) {
                if (transparent) {
                    this.state = [...Array(8)].map(() => true);
                } else {
                    this.state = [...Array(8)].map(() => false);
                }
            }
        },
        computed: {
            numberOfSections() {
                return this.subject.param1;
            },
            activeSections() {
                return this.state.map(v => v !== undefined);
            }
        },
    };
</script>

<style lang="scss">
    .digiglass-parameters-setter {
        $sizeSmaller: 200px;
        $sizeBigger: $sizeSmaller + 60px;
        position: relative;
        img.digiglass-bg {
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
                background: rgba(#fff, .5);
                flex-grow: 1;
                display: flex;
                align-items: center;
                img {
                    height: 20px;
                    margin: 0 10px;
                    display: none;
                }
                .digiglass-glass {
                    transition: .2s linear background-color;
                }
                &:not(.digiglass-section-opaque):not(.digiglass-section-transparent):hover {
                    /*.digiglass-glass {*/
                    background: rgba(#ccc, .5);
                    /*}*/
                }
                &.digiglass-section-opaque {
                    .digiglass-glass {
                        background: rgba(#4eb8e6, .9);
                    }
                    &:hover {
                        .digiglass-glass {
                            background: rgba(#4eb8e6, .99);
                        }
                    }
                    img.opaque-image {
                        display: inline-block;
                    }
                }
                &.digiglass-section-transparent {
                    .digiglass-glass {
                        background: rgba(#4eb8e6, .2);
                    }
                    &:hover {
                        .digiglass-glass {
                            background: rgba(#4eb8e6, .4);
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
                flex-direction: row-reverse;
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
                flex-direction: column-reverse;
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

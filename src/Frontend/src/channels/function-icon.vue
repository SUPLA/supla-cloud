<template>
    <span class="channel-icon"
        v-if="functionId !== undefined">
        <template v-if="model.userIconId || userIcon">
            <img :src="userIconSrc"
                :class="`icon-size-${width}`">
            <!-- Double icon display for HUMIDITYANDTEMPERATURE function. -->
            <img :src="userIconSrcForState(1)"
                v-if="functionId == 45"
                :class="`icon-size-${width}`">
        </template>
        <template v-else>
            <img :src="'/assets/img/functions/' + functionId + alternativeSuffix + stateSuffix + '.svg' | withBaseUrl"
                :width="width">
        </template>
    </span>
</template>

<script>
    import {withDownloadAccessToken} from "../common/filters";

    export default {
        props: ['model', 'width', 'alternative', 'userIcon'],
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
                if (this.model.state) {
                    if (this.model.state.hi || this.model.state.shut > 99
                        || (this.model.state.closed === true || this.model.state.closed > 99)) {
                        return '-closed';
                    }
                    if (this.model.state.partial_hi) {
                        return '-partial';
                    }
                    if (this.model.state.color_brightness !== undefined && this.model.state.brightness !== undefined) {
                        return '-' + (this.model.state.brightness ? 'on' : 'off') + (this.model.state.color_brightness ? 'on' : 'off');
                    } else if (this.model.state.color_brightness > 0 || this.model.state.brightness > 0) {
                        return '-on';
                    }
                    if (this.model.state.on === true) {
                        return '-on';
                    }
                }
                return '';
            },
            stateIndex() {
                const suffix = this.stateSuffix;
                if (['-closed', '-on', '-onoff'].indexOf(suffix) !== -1) {
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
            }
        },
        methods: {
            userIconSrcForState(stateIndex) {
                if (this.userIcon) {
                    return 'data:image/png;base64,' + this.userIcon.images[stateIndex];
                } else {
                    return withDownloadAccessToken(`/api/user-icons/${this.model.userIconId}/${stateIndex}?`);
                }
            }
        }
    };
</script>

<style lang="scss">
    .channel-icon {
        img {
            max-width: 100%;
            @for $i from 5 to 12 {
                $width: $i * 10;
                &.icon-size-#{$width} {
                    max-width: $width * 1px;
                    max-height: $width * 1px;
                }
            }
        }
    }
</style>

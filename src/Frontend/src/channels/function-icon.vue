<template>
    <span class="channel-icon"
        v-if="functionId !== undefined">
        <img :src="`/api/user-icons/${model.userIconId}/${stateIndex}?` | withDownloadAccessToken"
            v-if="model.userIconId"
            :class="`icon-size-${width}`">
        <img :src="'/assets/img/functions/' + functionId + alternativeSuffix + stateSuffix + '.svg' | withBaseUrl"
            :width="width"
            v-else>
    </span>
</template>

<script>
    export default {
        props: ['model', 'width', 'alternative'],
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
                    if (this.model.state.hi) {
                        return '-closed';
                    }
                    if (this.model.state.partial_hi) {
                        return '-partial';
                    }
                    if (this.model.state.color_brightness !== undefined && this.model.state.brightness !== undefined) {
                        return '-' + (this.model.state.brightness ? 'on' : 'off') + (this.model.state.color_brightness ? 'on' : 'off');
                    } else if (this.model.state.color_brightness === 0 || this.model.state.brightness === 0) {
                        return '-off';
                    }
                    if (this.model.state.on === false) {
                        return '-off';
                    }
                }
                return '';
            },
            stateIndex() {
                const suffix = this.stateSuffix;
                if (['-closed', '-off', '-onoff'].indexOf(suffix) !== -1) {
                    return 1;
                } else if (['-offon', '-partial'].indexOf(suffix) !== -1) {
                    return 2;
                } else if ('-offoff' === suffix) {
                    return 3;
                } else {
                    return 0;
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

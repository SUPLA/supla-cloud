<template>
    <span class="channel-icon">
        <img :src="'/assets/img/functions/' + functionId + alternativeSuffix + stateSuffix + '.svg' | withBaseUrl"
            :width="width"
            v-if="functionId !== undefined">
    </span>
</template>

<script>
    export default {
        props: ['model', 'width', 'alternative', 'state'],
        computed: {
            functionId() {
                if (this.model) {
                    if (this.model.functionId) {
                        return this.model.functionId;
                    } else if (this.model.function) {
                        return this.model.function.id;
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
                if (this.state) {
                    if (this.state.hi) {
                        return '-closed';
                    }
                    if (this.state.partial_hi) {
                        return '-partial';
                    }
                    if (this.state.on === false) {
                        return '-off';
                    }
                    if (this.state.color_brightness !== undefined && this.state.brightness !== undefined) {
                        return '-' + (this.state.brightness ? 'on' : 'off') + (this.state.color_brightness ? 'on' : 'off');
                    }
                    else if (this.state.color_brightness || this.state.brightness) {
                        return '-on';
                    }
                }
                return '';
            }
        }
    };
</script>

<style lang="scss">
    .channel-icon {
        img {
            max-width: 100%;
        }
    }
</style>

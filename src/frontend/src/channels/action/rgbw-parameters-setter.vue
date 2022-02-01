<template>
    <div>
        <div class="rgbw-parameter"
            v-if="hasBrightness">
            <label>{{ $t('Brightness') }}</label>
            <span class="input-group">
                <input type="number"
                    min="0"
                    max="100"
                    step="1"
                    class="form-control"
                    maxlength="3"
                    v-model="brightness"
                    @change="onChange()">
                <span class="input-group-addon">%</span>
            </span>
        </div>
        <hr v-if="hasBrightness && hasColor">
        <div class="rgbw-parameter"
            v-if="hasColor">
            <label>{{ $t('Color') }}</label>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="choose"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('Choose') }}
                </label>
            </div>
            <div v-if="hueMode == 'choose'">
                <hue-colorpicker v-model="hue"
                    @input="onChange()"></hue-colorpicker>
            </div>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="random"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('Random') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="white"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('White') }}
                </label>
            </div>
        </div>
        <div class="rgbw-parameter"
            v-if="hasColor">
            <label>{{ $t('Color brightness') }}</label>
            <span class="input-group">
                <input type="number"
                    min="0"
                    max="100"
                    step="1"
                    class="form-control"
                    maxlength="3"
                    v-model="colorBrightness"
                    @change="onChange()">
                <span class="input-group-addon">%</span>
            </span>
        </div>
    </div>
</template>

<script>
    import HueColorpicker from "./hue-colorpicker.vue";
    import ChannelFunction from "../../common/enums/channel-function";

    export default {
        components: {HueColorpicker},
        props: ['subject', 'value'],
        data() {
            return {
                hue: 0,
                hueMode: 'choose',
                colorBrightness: 0,
                brightness: 0,
            };
        },
        mounted() {
            if (this.value) {
                if (this.value.hue === 'random') {
                    this.hueMode = 'random';
                } else if (this.value.hue === 'white') {
                    this.hueMode = 'white';
                } else {
                    this.hue = this.value.hue || 0;
                }
                this.colorBrightness = this.value.color_brightness || 0;
                this.brightness = this.value.brightness || 0;
            }
            if (!this.value || Object.keys(this.value).length === 0) {
                if (this.subject.state) {
                    this.colorBrightness = this.subject.state.color_brightness || 0;
                    this.brightness = this.subject.state.brightness || 0;
                }
                this.onChange();
            }
        },
        methods: {
            onChange() {
                let value = {};
                if (this.hasBrightness) {
                    this.brightness = this.ensureBetween(this.brightness, 0, 100);
                    value.brightness = this.brightness;
                }
                if (this.hasColor) {
                    if (this.hueMode === 'choose') {
                        value.hue = this.ensureBetween(this.hue, 0, 360);
                    } else {
                        value.hue = this.hueMode === 'random' ? 'random' : 'white';
                    }
                    this.colorBrightness = this.ensureBetween(this.colorBrightness, 0, 100);
                    value.color_brightness = this.colorBrightness;
                }
                this.$emit('input', value);
            },
            ensureBetween(value, min, max) {
                if (value < min) {
                    return min;
                } else if (value > max) {
                    return max;
                } else {
                    return +value;
                }
            }
        },
        computed: {
            hasBrightness() {
                return [ChannelFunction.DIMMER, ChannelFunction.DIMMERANDRGBLIGHTING].includes(this.channelFunctionId);
            },
            hasColor() {
                return [ChannelFunction.RGBLIGHTING, ChannelFunction.DIMMERANDRGBLIGHTING].includes(this.channelFunctionId);
            },
            channelFunctionId() {
                return this.subject.function.id;
            },
        },
        watch: {
            channelFunction() {
                this.onChange();
            }
        }
    };
</script>

<style lang="scss">
    .rgbw-parameter {
        clear: both;
        width: 100%;
        margin-bottom: 1em;
        &:last-child {
            margin-bottom: 0;
        }
        .input-group-addon {
            border: 0 !important;
        }
    }
</style>

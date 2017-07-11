<template>
    <div class="row">
        <div :class="brightnessClass">
            <div class="form-group">
                <label>{{ $t('Brightness') }}</label>
                <span class="input-group">
                    <input type="number"
                        min="0"
                        max="100"
                        step="5"
                        class="form-control"
                        maxlength="3"
                        v-model="brightness"
                        @change="onChange()">
                    <span class="input-group-addon">%</span>
                </span>
            </div>
        </div>
        <div :class="hueClass">
            <div class="form-group">
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
        </div>
        <div :class="colorBrightnessClass">
            <div class="form-group">
                <label>{{ $t('Color brightness') }}</label>
                <span class="input-group">
                    <input type="number"
                        min="0"
                        max="100"
                        step="5"
                        class="form-control"
                        maxlength="3"
                        v-model="colorBrightness"
                        @change="onChange()">
                    <span class="input-group-addon">%</span>
                </span>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import HueColorpicker from "./hue-colorpicker.vue";

    export default {
        name: 'rgbw-parameters-setter',
        components: {HueColorpicker},
        props: ['channelFunction', 'value'],
        data() {
            return {
                hue: 0,
                hueMode: 'choose',
                colorBrightness: 0,
                brightness: 0
            };
        },
        mounted(){
            if (this.value) {
                if (this.value.hue == 'random') {
                    this.hueMode = 'random';
                } else if (this.value.hue == 'white') {
                    this.hueMode = 'white';
                } else {
                    this.hue = this.value.hue || 0;
                }
                this.colorBrightness = this.value.color_brightness || 0;
                this.brightness = this.value.brightness || 0;
            }
        },
        methods: {
            onChange() {
                let value = {};
                if (this.brightnessClass != 'hidden') {
                    value.brightness = this.brightness;
                }
                if (this.hueClass != 'hidden') {
                    value.hue = this.hueMode == 'choose' ? +this.hue : (this.hueMode == 'random' ? 'random' : 'white');
                }
                if (this.colorBrightnessClass != 'hidden') {
                    value.color_brightness = this.colorBrightness;
                }
                this.$emit('input', value);
            }
        },
        computed: {
            // 180 - DIMMER, 190 - RGBLIGHTING, 200 - DIMMERANDRGBLIGHTING
            brightnessClass() {
                return this.channelFunction == 180 ? 'col-xs-12' : (this.channelFunction == 190 ? 'hidden' : 'col-xs-4');
            },
            hueClass() {
                return this.channelFunction == 180 ? 'hidden' : (this.channelFunction == 190 ? 'col-xs-6' : 'col-xs-4');
            },
            colorBrightnessClass() {
                return this.channelFunction == 180 ? 'hidden' : (this.channelFunction == 190 ? 'col-xs-6' : 'col-xs-4');
            },
        },
        watch: {
            channelFunction(){
                this.onChange();
            }
        }
    };
</script>

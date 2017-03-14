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
        <div :class="colorClass">
            <div class="form-group">
                <label>{{ $t('Color') }}</label>
                <div class="clearfix">
                    <div class="pull-left"
                        v-show="!randomColor"
                        style="margin-right: 10px;vertical-align: middle;">
                        <hue-colorpicker v-model="color"
                            @input="onChange()"></hue-colorpicker>
                    </div>
                    <div class="checkbox pull-left">
                        <label>
                            <input type="checkbox"
                                v-model="randomColor"
                                @change="onChange()"> {{ $t('Random') }}
                        </label>
                    </div>
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
                color: 0,
                randomColor: false,
                colorBrightness: 0,
                brightness: 0
            };
        },
        mounted(){
            if (this.value) {
                if (this.value.color == 'random') {
                    this.randomColor = true;
                } else {
                    this.color = this.value.color || 0;
                }
                this.colorBrightness = this.value.colorBrightness || 0;
                this.brightness = this.value.brightness || 0;
            }
        },
        methods: {
            onChange() {
                let value = {};
                if (this.brightnessClass != 'hidden') {
                    value.brightness = this.brightness;
                }
                if (this.colorClass != 'hidden') {
                    value.color = this.randomColor ? 'random' : +this.color;
                }
                if (this.colorBrightnessClass != 'hidden') {
                    value.colorBrightness = this.colorBrightness;
                }
                this.$emit('input', value);
            }
        },
        computed: {
            // 180 - DIMMER, 190 - RGBLIGHTING, 200 - DIMMERANDRGBLIGHTING
            brightnessClass() {
                return this.channelFunction == 180 ? 'col-xs-12' : (this.channelFunction == 190 ? 'hidden' : 'col-xs-4');
            },
            colorClass() {
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

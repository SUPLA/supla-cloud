<template>
    <div>
        <vue-slider v-model="sliderValue"
            :data="possibleValues"
            tooltip="always"
            tooltip-placement="top"
            :tooltip-formatter="formattedValue"></vue-slider>
        <div class="pull-right">
            <div class="controls">
                <a @click="more()"><i class="glyphicon glyphicon-plus"></i></a>
                <a @click="less()"><i class="glyphicon glyphicon-minus"></i></a>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSlider from 'vue-slider-component';
    import 'vue-slider-component/theme/antd.css';

    export default {
        props: ['value'],
        components: {VueSlider},
        data() {
            return {
                sliderValue: 0,
                possibleValues: [250, 500, // ms
                    ...[...Array(30).keys()].map(k => k * 1000 + 1000), // s 1 - 30
                    ...[...Array(5).keys()].map(k => k * 5000 + 1000 * 35), // s 1 - 30
                    ...[...Array(30).keys()].map(k => k * 60000 + 60000), // min 1 - 30
                    ...[...Array(6).keys()].map(k => k * 5 * 60000 + 60000 * 35), // min 35-60
                ]
            };
        },
        mounted() {
            this.sliderValue = this.value;
            if (this.possibleValues.indexOf(this.sliderValue) === -1) {
                this.sliderValue = 1000;
            }
        },
        methods: {
            formattedValue(sliderValue = this.sliderValue) {
                const ms = sliderValue;
                if (ms < 1000) {
                    return ms + ' ms';
                } else if (ms < 60000) {
                    return Math.round(ms / 1000) + ' s';
                } else {
                    return Math.round(ms / 60000) + ' min';
                }
            },
            less() {
                const index = this.possibleValues.indexOf(this.sliderValue);
                this.sliderValue = this.possibleValues[Math.max(0, index - 1)];
            },
            more() {
                const index = this.possibleValues.indexOf(this.sliderValue);
                console.log(index, this.sliderValue);
                this.sliderValue = this.possibleValues[Math.min(this.possibleValues.length - 1, index + 1)];
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .vue-slider {
        .vue-slider-process {
            background-color: $supla-green;
        }
        .vue-slider-dot-handle {
            border-color: $supla-green;
        }
        &:hover {
            .vue-slider-process {
                background-color: darken($supla-green, 10%);
            }
            .vue-slider-dot-handle {
                border-color: darken($supla-green, 10%);
                &:hover {
                    border-color: $supla-green;
                }
            }
        }
    }
</style>

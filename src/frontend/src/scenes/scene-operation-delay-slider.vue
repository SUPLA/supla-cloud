<template>
    <div>
        <vue-slider v-model="sliderValue"
            @change="updateModel()"
            :data="possibleValues"
            :lazy="true"
            tooltip="always"
            tooltip-placement="top"
            :tooltip-formatter="formattedValue"></vue-slider>
        <div class="pull-right">
            <div class="controls">
                <a @click="more()"><i class="glyphicon glyphicon-plus"></i></a>
                <a @click="less()"><i class="glyphicon glyphicon-minus"></i></a>
                <a @click="$emit('delete')"><i class="glyphicon glyphicon-trash"></i></a>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSlider from 'vue-slider-component';
    import 'vue-slider-component/theme/antd.css';
    import {prettyMilliseconds} from "../common/filters";

    export default {
        props: ['value'],
        components: {VueSlider},
        data() {
            return {
                sliderValue: 0,
                possibleValues: [250, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, // ms
                    ...[...Array(26).keys()].map(k => k * 1000 + 5000), // s 5 - 30
                    ...[...Array(5).keys()].map(k => k * 5000 + 1000 * 35), // s 35 - 55
                    ...[...Array(30).keys()].map(k => k * 60000 + 60000), // min 1 - 30
                    ...[...Array(6).keys()].map(k => k * 5 * 60000 + 60000 * 35), // min 35-60
                ]
            };
        },
        mounted() {
            this.sliderValue = this.value;
            if (this.sliderValue > 0 && this.possibleValues.indexOf(this.sliderValue) === -1) {
                this.possibleValues.push(parseInt(this.sliderValue));
                this.possibleValues.sort((a, b) => a - b);
            }
        },
        methods: {
            formattedValue(sliderValue = this.sliderValue) {
                return prettyMilliseconds(+sliderValue, this);
            },
            less() {
                const index = this.possibleValues.indexOf(this.sliderValue);
                this.sliderValue = this.possibleValues[Math.max(0, index - 1)];
                this.updateModel();
            },
            more() {
                const index = this.possibleValues.indexOf(this.sliderValue);
                this.sliderValue = this.possibleValues[Math.min(this.possibleValues.length - 1, index + 1)];
                this.updateModel();
            },
            updateModel() {
                this.$emit('input', this.sliderValue);
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

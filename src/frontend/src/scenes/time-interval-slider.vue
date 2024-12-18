<template>
    <div>
        <vue-slider v-model="sliderValue"
            @change="updateModel()"
            :data="possibleValues"
            :lazy="true"
            class="green"
            tooltip="always"
            tooltip-placement="top"
            :tooltip-formatter="formattedValue"></vue-slider>
        <div class="pull-right">
            <div class="controls">
                <a @click="less()" class="mx-1"><i class="glyphicon glyphicon-minus"></i></a>
                <a @click="more()" class="mx-1"><i class="glyphicon glyphicon-plus"></i></a>
                <slot name="buttons"></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSlider from 'vue-slider-component';
    import 'vue-slider-component/theme/antd.css';
    import {prettyMilliseconds} from "../common/filters";

    export default {
        props: {
            value: Number,
            seconds: Boolean,
            min: {
                type: Number,
                default: 0,
            },
            max: {
                type: Number,
                default: 1000 * 60 * 60 * 24 * 365,
            }
        },
        components: {VueSlider},
        data() {
            return {
                sliderValue: 0,
                possibleValues: [250, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, // ms
                    ...[...Array(26).keys()].map(k => k * 1000 + 5000), // s 5 - 30
                    ...[...Array(5).keys()].map(k => k * 5000 + 1000 * 35), // s 35 - 55
                    ...[...Array(30).keys()].map(k => k * 60000 + 60000), // min 1 - 30
                    ...[...Array(5).keys()].map(k => k * 5 * 60000 + 60000 * 35), // min 35-60
                    ...[...Array(23).keys()].map(k => k * 3600000 + 3600000), // h 1-24
                    ...[...Array(29).keys()].map(k => k * 86400000 + 86400000), // d 1-30
                    ...[...Array(6).keys()].map(k => k * 86400000 * 30 + 86400000 * 30), // d 1-30
                ]
            };
        },
        beforeMount() {
            this.sliderValue = this.value * this.multiplier;
            if (this.sliderValue > 0 && this.possibleValues.indexOf(this.sliderValue) === -1) {
                this.possibleValues.push(parseInt(this.sliderValue));
                this.possibleValues.sort((a, b) => a - b);
            }
            this.possibleValues = this.possibleValues.filter(v => v >= this.min * this.multiplier && v <= this.max * this.multiplier);
        },
        methods: {
            formattedValue(sliderValue = this.sliderValue) {
                return prettyMilliseconds(+sliderValue);
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
                this.$emit('input', this.sliderValue / this.multiplier);
            }
        },
        computed: {
            multiplier() {
                return this.seconds ? 1000 : 1;
            }
        }
    };
</script>

<template>
    <div class="hvac-setpoints-setter">
        <div class="form-group form-group-sm my-1" v-if="heatAvailable">
            <span class="input-group">
                <span class="input-group-addon">
                    <IconHeating/>
                    {{ $t('Heat to') }}
                </span>
                <input type="number"
                    step="0.1"
                    :min="roomMin"
                    :max="roomMax"
                    class="form-control text-center"
                    @change="onChange('heat')"
                    v-model="temperatureHeat">
                <span class="input-group-addon">
                    &deg;C
                </span>
            </span>
        </div>
        <div class="form-group form-group-sm my-1" v-if="coolAvailable">
            <span class="input-group">
                <span class="input-group-addon">
                    <IconCooling/>
                    {{ $t('Cool to') }}
                </span>
                <input type="number"
                    step="0.1"
                    :min="roomMin"
                    :max="roomMax"
                    class="form-control text-center"
                    @change="onChange('cool')"
                    v-model="temperatureCool">
                <span class="input-group-addon">
                    &deg;C
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    import IconHeating from "@/common/icons/icon-heating.vue";
    import IconCooling from "@/common/icons/icon-cooling.vue";

    export default {
        components: {IconCooling, IconHeating},
        props: {
            subject: Object,
            value: Object,
            hideHeat: Boolean,
            hideCool: Boolean,
        },
        data() {
            return {
                temperatureHeat: 0,
                temperatureCool: 0,
            };
        },
        mounted() {
            this.temperatureHeat = Math.min(Math.max(this.roomMin, 21), this.roomMax);
            this.temperatureCool = Math.min(Math.max(this.roomMin, 23), this.roomMax);
            if (this.value) {
                this.initFromValue();
            } else {
                this.onChange();
            }
        },
        methods: {
            initFromValue() {
                this.temperatureHeat = this.value.heat;
                this.temperatureCool = this.value.cool;
            },
            onChange(changed) {
                if (this.hasHeat && this.hasCool) {
                    if (changed === 'heat' && this.hasCool) {
                        this.temperatureCool = Math.max(this.temperatureCool, +this.temperatureHeat + this.offsetMin);
                    }
                    if (changed === 'cool' && this.hasHeat) {
                        this.temperatureHeat = Math.min(this.temperatureHeat, +this.temperatureCool - this.offsetMin);
                    }
                }
                this.$emit('input', this.modelValue);
            },
        },
        computed: {
            offsetMin() {
                return this.subject.config.temperatureConstraints?.autoOffsetMin || 0;
            },
            temperatureConstraintName() {
                return this.subject.config?.defaultTemperatureConstraintName || 'room';
            },
            roomMin() {
                return this.subject.config?.temperatureConstraints?.[`${this.temperatureConstraintName}Min`] || 0;
            },
            roomMax() {
                return this.subject.config?.temperatureConstraints?.[`${this.temperatureConstraintName}Max`] || 100;
            },
            heatAvailable() {
                return !this.hideHeat && this.subject.config.heatingModeAvailable;
            },
            coolAvailable() {
                return !this.hideCool && this.subject.config.coolingModeAvailable;
            },
            hasHeat() {
                return this.temperatureHeat !== null && this.temperatureHeat !== '';
            },
            hasCool() {
                return this.temperatureCool !== null && this.temperatureCool !== '';
            },
            modelValue() {
                const modelValue = {};
                if (this.heatAvailable && this.hasHeat) {
                    modelValue.heat = +this.temperatureHeat;
                }
                if (this.coolAvailable && this.hasCool) {
                    modelValue.cool = +this.temperatureCool;
                }
                return modelValue;
            }
        },
        watch: {
            value() {
                if (this.value) {
                    const {heat, cool} = this.modelValue;
                    if ((this.heatAvailable && this.value.heat !== heat) || (this.coolAvailable && this.value.cool !== cool)) {
                        this.initFromValue();
                    }
                }
            }
        }
    };
</script>

<style lang="scss">
    .hvac-setpoints-setter {
        .input-group-addon {
            border: 0 !important;
        }
    }
</style>

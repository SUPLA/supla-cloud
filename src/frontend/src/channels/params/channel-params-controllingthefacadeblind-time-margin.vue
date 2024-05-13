<template>
    <div>


        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                data-toggle="dropdown">
                {{ $t(`timeMarginMode_${timeMarginMode}`) }}
                <span class="caret"></span>
            </button>
            <!-- i18n: ['timeMarginMode_off', 'timeMarginMode_device', 'timeMarginMode_custom'] -->
            <ul class="dropdown-menu">
                <li v-for="mode in ['off', 'device', 'custom']" :key="mode">
                    <a @click="timeMarginMode = mode; onChange()"
                        v-show="mode !== timeMarginMode">
                        {{ $t(`timeMarginMode_${mode}`) }}
                    </a>
                </li>
            </ul>
        </div>

        <transition-expand>
            <NumberInput v-model="timeMarginValue"
                v-if="timeMarginMode === 'custom'"
                :min="1" :max="100" suffix=" %"
                class="form-control text-center mt-2"
                @input="onChange()"/>
        </transition-expand>
    </div>
</template>

<script>
    import NumberInput from "@/common/number-input.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand, NumberInput},
        props: {
            value: [Number, String],
        },
        data() {
            return {
                timeMarginMode: 'off',
                timeMarginValue: 1,
            };
        },
        mounted() {
            this.initFromModel();
        },
        methods: {
            initFromModel() {
                if (this.value === 'DEVICE_SPECIFIC') {
                    this.timeMarginMode = 'device';
                } else if (this.value > 0) {
                    this.timeMarginMode = 'custom';
                    this.timeMarginValue = this.value;
                } else {
                    this.timeMarginMode = 'off';
                }
            },
            onChange() {
                let value = 0;
                if (this.timeMarginMode === 'device') {
                    value = 'DEVICE_SPECIFIC';
                } else if (this.timeMarginMode === 'custom') {
                    value = this.timeMarginValue;
                }
                this.$emit('input', value);
            }
        }
    };
</script>

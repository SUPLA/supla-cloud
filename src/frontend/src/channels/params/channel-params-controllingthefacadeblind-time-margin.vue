<template>
    <div>
        <toggler v-model="enabled" :label="$t('Enabled')" @input="onChange()"
            class="ml-3"/>
        <toggler v-if="enabled" v-model="deviceSpecific" :label="$t('Use device default')" @input="onChange()"
            class="ml-3"/>
        <transition-expand>
            <NumberInput v-model="timeMarginValue"
                v-if="enabled && !deviceSpecific"
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
                timeMarginValue: 1,
                deviceSpecific: true,
                enabled: false,
            };
        },
        methods: {
            onChange() {
                let value = 0;
                if (this.enabled) {
                    if (this.deviceSpecific) {
                        value = 'DEVICE_SPECIFIC';
                    } else {
                        value = this.timeMarginValue;
                    }
                }
                this.$emit('input', value);
            }
        }
    };
</script>

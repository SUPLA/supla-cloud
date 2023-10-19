<template>
    <div>

        <div v-if="subject.functionId === ChannelFunction.HVAC_THERMOSTAT_AUTO" class="mb-3">
            <div class="radio">
                <label>
                    <input type="radio" value="" v-model="mode" @change="onChange()">
                    {{ $t('Latest mode') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" value="HEAT" v-model="mode" @change="onChange()">
                    {{ $t('Heat') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" value="COOL" v-model="mode" @change="onChange()">
                    {{ $t('Cool') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" value="AUTO" v-model="mode" @change="onChange()">
                    {{ $t('Automatic') }}
                </label>
            </div>
        </div>
        <transition-expand>
            <div v-if="mode !== 'AUTO'">
                <div class="form-group">
                    <label class="checkbox2">
                        <input type="checkbox" v-model="temperaturesEnabled" @change="onChange()">
                        <span>{{ $t('Set new temperatures') }}</span>
                    </label>
                </div>
                <transition-expand>
                    <HvacSetpointsSetter v-if="temperaturesEnabled" v-model="param.setpoints" :subject="subject" @input="onChange()"
                        :hide-heat="mode === 'COOL'" :hide-cool="mode === 'HEAT'"
                        class="mb-3"/>
                </transition-expand>
            </div>
        </transition-expand>
        <DurationParamSetter v-model="param.durationMs" :with-calendar="executorMode" @input="onChange()" disable-ms/>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import HvacSetpointsSetter from "@/channels/action/hvac-setpoints-setter.vue";
    import {deepCopy} from "@/common/utils";
    import {isEqual} from "lodash";
    import ChannelFunction from "@/common/enums/channel-function";
    import DurationParamSetter from "@/channels/action/duration-param-setter.vue";

    export default {
        components: {DurationParamSetter, HvacSetpointsSetter, TransitionExpand},
        props: {
            subject: Object,
            value: Object,
            executorMode: Boolean,
        },
        data() {
            return {
                param: {
                    setpoints: {},
                    durationMs: 0,
                },
                mode: '',
                temperaturesEnabled: false,
                ChannelFunction,
            };
        },
        beforeMount() {
            if (this.value) {
                this.initFromValue();
            }
        },
        methods: {
            initFromValue() {
                if (this.value.setpoints) {
                    this.temperaturesEnabled = true;
                    this.param.setpoints = deepCopy(this.value.setpoints);
                } else {
                    this.temperaturesEnabled = false;
                }
                this.param.durationMs = deepCopy(this.value.durationMs);
                this.param.mode = deepCopy(this.value.mode) || '';
            },
            onChange() {
                this.$nextTick(() => {
                    this.$emit('input', deepCopy(this.modelValue));
                })
            },
        },
        computed: {
            modelValue() {
                const modelValue = {};
                if (this.temperaturesEnabled) {
                    modelValue.setpoints = this.param.setpoints;
                }
                modelValue.durationMs = this.param.durationMs;
                if (this.mode) {
                    modelValue.mode = this.mode;
                }
                return modelValue;
            }
        },
        watch: {
            value() {
                if (this.value) {
                    const modelValue = this.modelValue;
                    if (!isEqual(this.value, modelValue)) {
                        this.initFromValue();
                    }
                }
            }
        }
    };
</script>


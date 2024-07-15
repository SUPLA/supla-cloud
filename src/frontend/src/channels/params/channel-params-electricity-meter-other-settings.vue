<template>
    <div>
        <dl v-if="channel.config.availableCTTypes && channel.config.availableCTTypes.length > 0">
            <dd>
                {{ $t('Used current transformer type') }}
                <!--                        <a @click="algorithmHelpShown = !algorithmHelpShown"><i class="pe-7s-help1"></i></a>-->
            </dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                        <span v-if="channel.config.usedCTType">{{ $t(`usedCTType_${channel.config.usedCTType}`) }}</span>
                        <span v-else>?</span>
                        <span class="caret ml-2"></span>
                    </button>
                    <!-- i18n:['usedCTType_100A_33mA', 'usedCTType_200A_66mA', 'usedCTType_400A_133mA'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in channel.config.availableCTTypes" :key="type">
                            <a @click="channel.config.usedCTType = type; $emit('change')"
                                v-show="type !== channel.config.usedCTType">
                                {{ $t(`usedCTType_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </dt>
        </dl>
        <dl v-if="channel.config.availablePhaseLedTypes && channel.config.availablePhaseLedTypes.length > 0">
            <dd>
                {{ $t('Phase LED type') }}
                <!--                        <a @click="algorithmHelpShown = !algorithmHelpShown"><i class="pe-7s-help1"></i></a>-->
            </dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                        <span>{{ $t(`usedPhaseLedType_${channel.config.usedPhaseLedType}`) }}</span>
                        <span class="caret ml-2"></span>
                    </button>
                    <!-- i18n:['usedPhaseLedType_OFF', 'usedPhaseLedType_VOLTAGE_PRESENCE', 'usedPhaseLedType_VOLTAGE_PRESENCE_INVERTED'] -->
                    <!-- i18n:['usedPhaseLedType_VOLTAGE_LEVEL', 'usedPhaseLedType_POWER_ACTIVE_DIRECTION'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in channel.config.availablePhaseLedTypes" :key="type">
                            <a @click="channel.config.usedPhaseLedType = type; $emit('change')"
                                v-show="type !== channel.config.usedPhaseLedType">
                                {{ $t(`usedPhaseLedType_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </dt>
        </dl>
        <transition-expand>
            <div v-if="channel.config.usedPhaseLedType === 'VOLTAGE_LEVEL'">
                <dl>
                    <dd>{{ $t('Low threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="lowV"
                            :min="0"
                            :max="highV || 1000"
                            :precision="2"
                            suffix=" V"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <dl>
                    <dd>{{ $t('High threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="highV"
                            :min="lowV || 0"
                            :max="1000"
                            :precision="2"
                            suffix=" V"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <div class="small">
                    <div>
                        <fa icon="circle" class="blue"/>
                        {{ $t('blue, when voltage lower than the low threshold') }}
                    </div>
                    <div>
                        <fa icon="circle" class="red"/>
                        {{ $t('red, when voltage higher than the high threshold') }}
                    </div>
                    <div>
                        <fa icon="circle" class="green"/>
                        {{ $t('green, otherwise') }}
                    </div>
                </div>
            </div>
        </transition-expand>
        <transition-expand>
            <div v-if="channel.config.usedPhaseLedType === 'POWER_ACTIVE_DIRECTION'">
                <dl>
                    <dd>{{ $t('Low threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="lowW"
                            :min="-100000"
                            :max="highW || 100000"
                            :precision="2"
                            suffix=" W"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <dl>
                    <dd>{{ $t('High threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="highW"
                            :min="lowW || -100000"
                            :max="100000"
                            :precision="2"
                            suffix=" W"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <div class="small">
                    <div>
                        <fa icon="circle" class="green"/>
                        {{ $t('green, when power less than the low threshold') }}
                    </div>
                    <div>
                        <fa icon="circle" class="red"/>
                        {{ $t('red, when power higher than the high threshold') }}
                    </div>
                    <div>
                        <fa icon="circle" class="blue"/>
                        {{ $t('blue, otherwise') }}
                    </div>
                </div>
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import NumberInput from "@/common/number-input.vue";
    import {ref, watch} from "vue";

    const props = defineProps({channel: Object});

    const lowV = ref(220);
    const highV = ref(250);
    const lowW = ref(-100);
    const highW = ref(100);

    if (props.channel.config.usedPhaseLedType === 'VOLTAGE_LEVEL') {
        lowV.value = props.channel.config.phaseLedParam1 || 220;
        highV.value = props.channel.config.phaseLedParam2 || 250;
    } else if (props.channel.config.usedPhaseLedType === 'POWER_ACTIVE_DIRECTION') {
        lowW.value = props.channel.config.phaseLedParam1 || 220;
        highW.value = props.channel.config.phaseLedParam2 || 250;
    }

    watch(() => props.channel.config.usedPhaseLedType, () => {
        if (props.channel.config.usedPhaseLedType === 'VOLTAGE_LEVEL') {
            props.channel.config.phaseLedParam1 = lowV.value;
            props.channel.config.phaseLedParam2 = highV.value;
        } else if (props.channel.config.usedPhaseLedType === 'POWER_ACTIVE_DIRECTION') {
            props.channel.config.phaseLedParam1 = lowW.value;
            props.channel.config.phaseLedParam2 = highW.value;
        }
    });

    watch(lowV, (v) => props.channel.config.phaseLedParam1 = v);
    watch(highV, (v) => props.channel.config.phaseLedParam2 = v);
    watch(lowW, (v) => props.channel.config.phaseLedParam1 = v);
    watch(highW, (v) => props.channel.config.phaseLedParam2 = v);
</script>

<style lang="scss" scoped>
    .blue {
        color: dodgerblue;
    }

    .red {
        color: orangered;
    }

    .green {
        color: green;
    }
</style>

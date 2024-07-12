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
                        <span v-if="channel.config.usedCTType">{{ channel.config.usedCTType.replace(/_/g, ' ') }}</span>
                        <span v-else>?</span>
                        <span class="caret ml-2"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li v-for="type in channel.config.availableCTTypes" :key="type">
                            <a @click="channel.config.usedCTType = type; $emit('change')"
                                v-show="type !== channel.config.usedCTType">
                                {{ type.replace(/_/g, ' ') }}
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
                        <NumberInput v-model="channel.config.phaseLedParam1"
                            :min="0"
                            :max="channel.config.phaseLedParam2 || 400"
                            :precision="2"
                            suffix=" V"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <dl>
                    <dd>{{ $t('High threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="channel.config.phaseLedParam2"
                            :min="channel.config.phaseLedParam1 || 0"
                            :max="400"
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
                        <NumberInput v-model="channel.config.phaseLedParam1"
                            :min="0"
                            :max="channel.config.phaseLedParam2 || 10000"
                            :precision="2"
                            suffix=" W"
                            class="form-control text-center mt-2"
                            @input="$emit('change')"/>
                    </dt>
                </dl>
                <dl>
                    <dd>{{ $t('High threshold') }}</dd>
                    <dt>
                        <NumberInput v-model="channel.config.phaseLedParam2"
                            :min="channel.config.phaseLedParam1 || 0"
                            :max="10000"
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
                </div>
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import NumberInput from "@/common/number-input.vue";

    defineProps({channel: Object});
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

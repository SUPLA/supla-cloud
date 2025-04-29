<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Role') }}</label>
            <div>
                <div class="btn-group">
                    <button type="button" @click="modbusConfig.role = 'MASTER'; onChange()"
                        :class="['btn', modbusConfig.role === 'MASTER' ? 'btn-green' : 'btn-white']">
                        {{ $t('Master (client)') }}
                    </button>
                    <button type="button" @click="modbusConfig.role = 'SLAVE'; onChange()"
                        :class="['btn', modbusConfig.role === 'SLAVE' ? 'btn-green' : 'btn-white']">
                        {{ $t('Slave (server)') }}
                    </button>
                    <button type="button" @click="modbusConfig.role = 'NOT_SET'; onChange()"
                        :class="['btn', modbusConfig.role === 'NOT_SET' ? 'btn-green' : 'btn-white']">
                        {{ $t('Disabled') }}
                    </button>
                </div>
            </div>
        </div>
        <transition-expand>
            <div class="form-group with-border-bottom" v-if="modbusConfig.role === 'SLAVE'">
                <label>{{ $t('Address') }}</label>
                <NumberInput v-model="modbusConfig.modbusAddress" :min="1" :max="247" @input="onChange()"/>
                <div class="help-block">{{ $t('Please specify a valid address between 1 and 247.') }}</div>
            </div>
        </transition-expand>
        <transition-expand>
            <div class="form-group with-border-bottom" v-if="modbusConfig.role === 'MASTER'">
                <label>{{ $t('Slave timeout (ms)') }}</label>
                <div>
                    <label class="checkbox2 checkbox2-grey">
                        <input type="checkbox" v-model="slaveTimeoutMsDefault" @change="onChange()">
                        {{ $t('Default (exact value depends on a device)') }}
                    </label>
                </div>
                <transition-expand>
                    <NumberInput v-if="!slaveTimeoutMsDefault" v-model="modbusConfig.slaveTimeoutMs" :min="1" :max="10000" suffix=" ms"
                        @input="onChange()"/>
                </transition-expand>
            </div>
        </transition-expand>
        <div v-if="modbusConfig.role !== 'NOT_SET'">
            <div class="form-group with-border-bottom">
                <div class="d-flex">
                    <h4 class="flex-grow-1">{{ $t('Serial') }}</h4>
                    <div>
                        <label class="checkbox2 checkbox2-grey">
                            <input type="checkbox" v-model="modbusSerialEnabled" @change="onChange()">
                            {{ $t('Enabled') }}
                        </label>
                    </div>
                </div>
                <transition-expand>
                    <div v-if="modbusSerialEnabled">
                        <div class="form-group">
                            <label>{{ $t('Mode') }}</label>
                            <SimpleDropdown v-model="modbusConfig.serial.mode" :options="modbusConfig.availableProtocols"
                                @input="onChange()"/>
                        </div>
                        <div class="form-group">
                            <label>{{ $t('Baudrate') }}</label>
                            <SimpleDropdown v-model="modbusConfig.serial.baudrate" :options="modbusConfig.availableBaudrates"
                                @input="onChange()"/>
                        </div>
                        <div class="form-group">
                            <label>{{ $t('Stop bits') }}</label>
                            <SimpleDropdown v-model="modbusConfig.serial.stopBits" :options="modbusConfig.availableStopbits"
                                @input="onChange()"/>
                        </div>
                    </div>
                </transition-expand>
            </div>
            <div class="form-group">
                <div class="d-flex">
                    <h4 class="flex-grow-1">{{ $t('Network') }}</h4>
                    <div>
                        <label class="checkbox2 checkbox2-grey">
                            <input type="checkbox" v-model="modbusNetworkEnabled" @change="onChange()">
                            {{ $t('Enabled') }}
                        </label>
                    </div>
                </div>
                <transition-expand>
                    <div v-if="modbusNetworkEnabled">
                        <div class="form-group">
                            <label>{{ $t('Mode') }}</label>
                            <SimpleDropdown v-model="modbusConfig.network.mode" :options="['TCP', 'UDP']" @input="onChange()"/>
                        </div>
                        <div class="form-group">
                            <label>{{ $t('Port') }}</label>
                            <NumberInput v-model="modbusConfig.network.port" :min="1" :max="65535" @input="onChange()"/>
                        </div>
                    </div>
                </transition-expand>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {computed, ref, watch} from "vue";
    import {deepCopy} from "@/common/utils";
    import NumberInput from "@/common/number-input.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";

    const props = defineProps({value: Object, config: Object});
    const emit = defineEmits(['input'])

    const modbusConfig = ref({});
    const readModbusConfig = () => modbusConfig.value = deepCopy(props.value);
    readModbusConfig();
    watch(() => props.value, () => readModbusConfig());

    const slaveTimeoutMsDefault = computed({
        get: () => modbusConfig.value.slaveTimeoutMs === 0,
        set: (value) => modbusConfig.value.slaveTimeoutMs = value ? 0 : 500,
    });

    const modbusSerialEnabled = computed({
        get: () => modbusConfig.value.serial.mode !== 'DISABLED',
        set: (value) => modbusConfig.value.serial.mode = value ? modbusConfig.value.availableProtocols[0] : 'DISABLED',
    });

    const modbusNetworkEnabled = computed({
        get: () => modbusConfig.value.network.mode !== 'DISABLED',
        set: (value) => modbusConfig.value.network.mode = value ? 'TCP' : 'DISABLED',
    });

    function onChange() {
        emit('input', deepCopy(modbusConfig.value));
    }
</script>

<script setup>
    import {computed} from "vue";

    const props = defineProps({
        channel: Object,
        masterThermostat: Object,
        setting: String,
    });

    const emit = defineEmits(['change']);

    const canChangeSetting = computed(() => !props.channel.config.readOnlyConfigFields?.includes(props.setting));
    const sameAsMaster = computed(() => +props.masterThermostat?.config[props.setting] === +props.channel.config[props.setting]);

    const setSameAsMaster = () => {
        props.channel.config[props.setting] = props.masterThermostat.config[props.setting];
        emit('change');
    }
</script>

<template>
    <div class="help-block small" v-if="masterThermostat && canChangeSetting">
        <span v-if="sameAsMaster">{{ $t('Same as on master thermostat.') }}</span>
        <span v-else>
            {{ $t('Different than on master thermostat.') }}
            <a @click="setSameAsMaster()">{{ $t('Change to be the same.') }}</a>
        </span>
    </div>
</template>

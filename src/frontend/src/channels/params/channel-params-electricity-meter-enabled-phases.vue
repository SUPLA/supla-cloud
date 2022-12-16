<template>
    <div>
        <dl>
            <dd>{{ $t('Enabled phases') }}</dd>
            <dt class="text-center">
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + (enabledPhases.includes(phaseNo) ? 'btn-green' : 'btn-default')"
                        v-for="phaseNo in (props.channel.config.availablePhases || [1,2,3])"
                        :key="phaseNo"
                        @click="togglePhase(phaseNo)">
                        {{ phaseNo }}
                    </a>
                </div>
            </dt>
        </dl>
        <transition-expand>
            <div class="alert alert-warning" v-if="errorShown">
                {{ $t('You must leave at least one phase enabled.') }}
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import {computed, ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand";

    const props = defineProps({channel: Object});
    const emit = defineEmits(['change'])

    const enabledPhases = computed(() => props.channel.config.enabledPhases || []);

    const errorShown = ref(false);

    let errorHideOn = undefined;
    const showError = () => {
        errorShown.value = true;
        const ERROR_SHOWN_DURATION = 3000;
        errorHideOn = Date.now() + ERROR_SHOWN_DURATION - 1;
        setTimeout(() => errorHideOn > Date.now() || (errorShown.value = false), ERROR_SHOWN_DURATION);
    };

    const togglePhase = (phaseNo) => {
        if (enabledPhases.value.length === 1 && enabledPhases.value[0] === phaseNo) {
            showError();
        } else {
            errorShown.value = false;
            if (!props.channel.config.disabledPhases) {
                props.channel.config.disabledPhases = [];
            }
            if (!props.channel.config.enabledPhases) {
                props.channel.config.enabledPhases = [];
            }
            if (enabledPhases.value.includes(phaseNo)) {
                props.channel.config.disabledPhases.push(phaseNo);
                props.channel.config.enabledPhases.splice(props.channel.config.enabledPhases.indexOf(phaseNo), 1);
            } else {
                props.channel.config.enabledPhases.push(phaseNo);
                props.channel.config.disabledPhases.splice(props.channel.config.disabledPhases.indexOf(phaseNo), 1);
            }
            emit('change');
        }
    };
</script>


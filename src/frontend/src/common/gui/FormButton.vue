<script setup>
    import {computed} from "vue";

    const props = defineProps({
        loading: Boolean,
        disabled: Boolean,
        disabledReason: String,
        submit: Boolean,
        buttonClass: String,
    });

    defineEmits(['click']);

    const buttonType = computed(() => props.submit ? 'submit' : 'button');
    const isDisabled = computed(() => !!(props.loading || props.disabled || props.disabledReason));
</script>

<template>
    <span v-tooltip="$t(disabledReason)">
        <button :type="buttonType" :class="['btn', buttonClass, {'disabled': isDisabled}]" :disabled="isDisabled" @click="$emit('click')">
            <button-loading-dots v-if="loading"/>
            <slot v-else/>
        </button>
    </span>
</template>

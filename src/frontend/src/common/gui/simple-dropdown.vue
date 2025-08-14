<script setup>
    import {computed} from "vue";

    const props = defineProps({options: Array, value: [String, Number]});
    const emit = defineEmits(['input']);

    const modelValue = computed({
        get: () => props.value,
        set: (value) => emit('input', value),
    });

</script>

<template>
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
            data-toggle="dropdown">
            <slot name="button" :value="modelValue">{{ modelValue }}</slot>
            {{ ' ' }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li v-for="option in options" :key="option">
                <a @click="modelValue = option" v-show="modelValue !== option">
                    <slot name="option" :option="option">{{ option }}</slot>
                </a>
            </li>
        </ul>
    </div>
</template>

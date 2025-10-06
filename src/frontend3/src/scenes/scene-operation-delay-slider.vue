<template>
    <TimeIntervalSlider v-model="modelValue" :max="3600000">
        <template #buttons>
            <a @click="$emit('delete')"><i class="glyphicon glyphicon-trash"></i></a>
        </template>
    </TimeIntervalSlider>
</template>

<script>
    import TimeIntervalSlider from "@/scenes/time-interval-slider.vue";

    export default {
        props: ['value'],
        components: {TimeIntervalSlider},
        computed: {
            modelValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            }
        }
    };
</script>

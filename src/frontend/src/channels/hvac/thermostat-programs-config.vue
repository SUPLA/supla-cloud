<template>
    <div class="container">
        KONFIG

        <a @click="currentProgram = 1">1</a>
        <a @click="currentProgram = 2">2</a>
        <a @click="currentProgram = 3">3</a>
        <a @click="currentProgram = 4">4</a>
        <a @click="currentProgram = 0">0</a>

        <WeekScheduleSelector v-model="week" :selection-mode="currentProgram"
            quarters
            class="week-schedule-selector-thermostat"/>

        {{ week }}
    </div>
</template>

<script>
    import WeekScheduleSelector from "@/access-ids/week-schedule-selector.vue";

    export default {
        components: {WeekScheduleSelector},
        props: {
            subject: Object,
        },
        data() {
            return {
                currentProgram: 2,
                week: {},
            }
        },
    };
</script>

<style lang="scss">
    @import '../../styles/variables';
    @import '../../styles/mixins';

    @mixin timeSlotColor($color, $colorHover) {
        background: $color;
        @media (hover: hover) {
            &:hover {
                background: $colorHover;
            }
        }
    }

    .week-schedule-selector-thermostat table.week-schedule-selector {
        .time-slot.time-slot-mode-1 {
            @include timeSlotColor($supla-blue, lighten($supla-blue, 20%));
        }
        .time-slot.time-slot-mode-2 {
            @include timeSlotColor($supla-green, lighten($supla-green, 20%));
        }
        .time-slot.time-slot-mode-3 {
            @include timeSlotColor($supla-orange, lighten($supla-orange, 20%));
        }
        .time-slot.time-slot-mode-4 {
            @include timeSlotColor($supla-red, lighten($supla-red, 20%));
        }
    }
</style>

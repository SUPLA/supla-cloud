<template>
    <div class="container">
        <pending-changes-page
            :header="$t('Thermostat programs')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveWeeklySchedules()"
            :is-pending="hasPendingChanges">
            KONFIG
            <a @click="currentProgram = 1">1</a>
            <a @click="currentProgram = 2">2</a>
            <a @click="currentProgram = 3">3</a>
            <a @click="currentProgram = 4">4</a>
            <a @click="currentProgram = 0">0</a>
            <WeekScheduleSelector v-model="quarters" :selection-mode="currentProgram"
                quarters
                class="week-schedule-selector-thermostat"/>
        </pending-changes-page>
    </div>
</template>

<script>
    import WeekScheduleSelector from "@/access-ids/week-schedule-selector.vue";
    import {mapValues} from "lodash";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import EventBus from "@/common/event-bus";
    import {deepCopy} from "@/common/utils";

    export default {
        components: {PendingChangesPage, WeekScheduleSelector},
        props: {
            subject: Object,
        },
        data() {
            return {
                hasPendingChanges: false,
                currentProgram: 2,
                initialConfig: undefined,
                weeklySchedule: undefined,
            }
        },
        beforeMount() {
            this.weeklySchedule = deepCopy(this.subject.config.weeklySchedule);
        },
        methods: {
            saveWeeklySchedules() {
                return this.$http.put(`channels/${this.subject.id}`, {
                    config: {
                        weeklySchedule: this.weeklySchedule,
                    }
                }).then(() => {
                    this.hasPendingChanges = false;
                    EventBus.$emit('channel-updated');
                });
            },
            cancelChanges() {
                this.weeklySchedule = deepCopy(this.subject.config.weeklySchedule);
                this.hasPendingChanges = false;
            }
        },
        computed: {
            quarters: {
                get() {
                    const quarters = {1: {}, 2: {}, 3: {}, 4: {}, 5: {}, 6: {}, 7: {}};
                    this.weeklySchedule.quarters.forEach((e, i) => {
                        quarters[Math.floor(i / 96) + 1][i % 96] = e;
                    });
                    return quarters;
                },
                set(value) {
                    const quarters = [];
                    mapValues(value, (q, weekday) => {
                        mapValues(q, (programId, index) => {
                            quarters[(+weekday - 1) * 96 + +index] = programId;
                        });
                    });
                    this.weeklySchedule.quarters = quarters;
                    this.hasPendingChanges = true;
                },
            }
        }
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

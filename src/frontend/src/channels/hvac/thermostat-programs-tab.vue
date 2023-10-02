<template>
    <div class="container">
        <pending-changes-page
            :header="$t('Thermostat programs')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveWeeklySchedules()"
            :is-pending="hasPendingChanges">

            <div class="text-center mb-5" v-if="subject.config.altWeeklySchedule">
                <a :class="['btn mx-2', editingMode === 'weeklySchedule' ? 'btn-orange' : 'btn-default']"
                    @click="editingMode = 'weeklySchedule'">
                    <IconHeating/>
                    {{ $t('Heating mode') }}
                </a>
                <a :class="['btn mx-2', editingMode === 'altWeeklySchedule' ? 'btn-blue' : 'btn-default']"
                    @click="editingMode = 'altWeeklySchedule'">
                    <IconCooling/>
                    {{ $t('Cooling mode') }}
                </a>
            </div>

            <ThermostatProgramsConfigurator v-model="editingSchedule.programSettings" :subject="subject"
                @input="hasPendingChanges = true"
                @programChosen="(programNo) => currentProgram = programNo"/>

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
    import IconHeating from "@/common/icons/icon-heating.vue";
    import IconCooling from "@/common/icons/icon-cooling.vue";
    import ThermostatProgramsConfigurator from "@/channels/hvac/thermostat-programs-configurator.vue";

    export default {
        components: {ThermostatProgramsConfigurator, IconCooling, IconHeating, PendingChangesPage, WeekScheduleSelector},
        props: {
            subject: Object,
        },
        data() {
            return {
                editingMode: 'weeklySchedule',
                hasPendingChanges: false,
                currentProgram: false,
                initialConfig: undefined,
                weeklySchedule: undefined,
                altWeeklySchedule: undefined,
            }
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            saveWeeklySchedules() {
                return this.$http.put(`channels/${this.subject.id}`, {
                    config: {
                        weeklySchedule: this.weeklySchedule,
                        altWeeklySchedule: this.altWeeklySchedule,
                    }
                }).then(() => {
                    this.hasPendingChanges = false;
                    EventBus.$emit('channel-updated');
                });
            },
            cancelChanges() {
                this.weeklySchedule = deepCopy(this.subject.config.weeklySchedule);
                this.altWeeklySchedule = deepCopy(this.subject.config.altWeeklySchedule);
                this.hasPendingChanges = false;
            }
        },
        computed: {
            editingSchedule() {
                return this[this.editingMode];
            },
            quarters: {
                get() {
                    const quarters = {1: {}, 2: {}, 3: {}, 4: {}, 5: {}, 6: {}, 7: {}};
                    this.editingSchedule.quarters.forEach((e, i) => {
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
                    this.editingSchedule.quarters = quarters;
                    this.hasPendingChanges = true;
                },
            }
        }
    };
</script>

<style lang="scss">
    @import '../../styles/variables';
    @import '../../styles/mixins';

    @mixin timeSlotColor($programNo, $color, $colorHover) {
        .week-schedule-selector-thermostat table.week-schedule-selector {
            .time-slot.time-slot-mode-#{$programNo} {
                background: $color;
            }
            &.selection-mode-#{$programNo} {
                .time-slot {
                    @media (hover: hover) {
                        &:hover {
                            background: $colorHover;
                        }
                    }
                }
            }
        }
        .thermostat-program-button-#{$programNo} {
            border: 2px solid $color;
            &.chosen {
                background: $colorHover;
            }
        }
    }

    $program0Color: #B4B7BA;
    $program1Color: #8C9DFF;
    $program2Color: #B0E0A8;
    $program3Color: #FFD19A;
    $program4Color: #FFAA8C;

    @include timeSlotColor(0, $program0Color, lighten($program0Color, 10%));
    @include timeSlotColor(1, $program1Color, lighten($program1Color, 10%));
    @include timeSlotColor(2, $program2Color, lighten($program2Color, 10%));
    @include timeSlotColor(3, $program3Color, lighten($program3Color, 10%));
    @include timeSlotColor(4, $program4Color, lighten($program4Color, 10%));
</style>

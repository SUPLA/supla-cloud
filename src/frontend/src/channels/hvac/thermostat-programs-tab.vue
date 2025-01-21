<template>
    <div class="container">
        <pending-changes-page
            :header="$t('Thermostat programs')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveWeeklySchedules()"
            :is-pending="!subject.hasPendingChanges && hasPendingChanges && !editingPrograms">

            <div class="row" v-if="subject.config.masterThermostatChannelId">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="alert alert-info">
                        {{ $t('Thermostat program settings are inherited from the master thermostat for this channel. Please visit its configuration to alter it.') }}
                        <router-link :to="{name: 'channel.thermostatPrograms', params: {id: subject.config.masterThermostatChannelId}}">
                            {{ $t('Visit the master thermostat page.') }}
                        </router-link>
                    </div>
                </div>
            </div>

            <div v-else>
                <transition-expand>
                    <div class="row" v-if="conflictingConfig">
                        <div class="col-sm-6 col-sm-offset-3">
                            <ConfigConflictWarning @refresh="replaceConfigWithConflictingConfig()"/>
                        </div>
                    </div>
                </transition-expand>

                <div class="text-center mb-5"
                    v-if="subject.config.altWeeklySchedule && subject.config.heatingModeAvailable && subject.config.coolingModeAvailable">
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
                    :default-program-mode="editingMode === 'weeklySchedule' ? 'HEAT' : 'COOL'"
                    @editing="editingPrograms = $event"
                    @input="hasPendingChanges = true"
                    @programChosen="(programNo) => currentProgram = programNo"/>

                <WeekScheduleSelector v-model="quarters" :selection-mode="currentProgram"
                    quarters
                    class="week-schedule-selector-thermostat"/>
            </div>
        </pending-changes-page>
    </div>
</template>

<script>
    import WeekScheduleSelector from "@/activity/week-schedule-selector.vue";
    import {mapValues} from "lodash";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import EventBus from "@/common/event-bus";
    import {deepCopy} from "@/common/utils";
    import IconHeating from "@/common/icons/icon-heating.vue";
    import IconCooling from "@/common/icons/icon-cooling.vue";
    import ThermostatProgramsConfigurator from "@/channels/hvac/thermostat-programs-configurator.vue";
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {
            TransitionExpand,
            ConfigConflictWarning,
            ThermostatProgramsConfigurator, IconCooling, IconHeating, PendingChangesPage, WeekScheduleSelector
        },
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
                conflictingConfig: undefined,
                editingPrograms: false,
            }
        },
        beforeMount() {
            this.cancelChanges();
            this.editingMode = this.subject.config.subfunction === 'COOL' ? 'altWeeklySchedule' : 'weeklySchedule';
        },
        methods: {
            saveWeeklySchedules() {
                return this.$http.put(`channels/${this.subject.id}`, {
                    config: {
                        weeklySchedule: this.weeklySchedule,
                        altWeeklySchedule: this.altWeeklySchedule,
                    },
                    configBefore: this.subject.configBefore,
                }, {skipErrorHandler: [409]}).then(() => {
                    this.hasPendingChanges = false;
                    EventBus.$emit('channel-updated');
                }).catch(response => {
                    if (response.status === 409) {
                        this.conflictingConfig = response.body.details.config;
                    }
                });
            },
            cancelChanges() {
                this.weeklySchedule = deepCopy(this.subject.config.weeklySchedule);
                this.altWeeklySchedule = deepCopy(this.subject.config.altWeeklySchedule);
                this.hasPendingChanges = false;
            },
            replaceConfigWithConflictingConfig() {
                this.subject.config = this.conflictingConfig;
                this.subject.configBefore = deepCopy(this.subject.config);
                this.conflictingConfig = false;
                this.cancelChanges();
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

    $program0Color: #b4b7ba;
    $program1Color: #8c9dff;
    $program2Color: #b0e0a8;
    $program3Color: #ffd19a;
    $program4Color: #ffaa8c;

    @include timeSlotColor(0, $program0Color, lighten($program0Color, 10%));
    @include timeSlotColor(1, $program1Color, lighten($program1Color, 10%));
    @include timeSlotColor(2, $program2Color, lighten($program2Color, 10%));
    @include timeSlotColor(3, $program3Color, lighten($program3Color, 10%));
    @include timeSlotColor(4, $program4Color, lighten($program4Color, 10%));
</style>

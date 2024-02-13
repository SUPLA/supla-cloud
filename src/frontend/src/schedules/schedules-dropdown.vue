<template>
    <div>
        <div class="select-loader"
            v-if="!schedules">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="selectpicker"
            :disabled="!schedules"
            ref="dropdown"
            data-live-search="true"
            data-width="100%"
            data-style="btn-default btn-wrapped"
            v-model="chosenSchedule"
            @change="$emit('input', chosenSchedule)">
            <option v-for="schedule in schedulesForDropdown"
                :key="schedule.id"
                :value="schedule"
                :data-content="scheduleHtml(schedule)">
                {{ scheduleCaption(schedule) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import "@/common/bootstrap-select";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import $ from "jquery";

    export default {
        props: ['value', 'filter'],
        components: {ButtonLoadingDots},
        data() {
            return {
                schedules: undefined,
                chosenSchedule: undefined
            };
        },
        mounted() {
            this.fetchSchedules();
        },
        methods: {
            fetchSchedules() {
                this.schedules = undefined;
                this.$http.get('schedules?include=closestExecutions').then(({body: schedules}) => {
                    this.schedules = schedules.filter(this.filter || (() => true));
                    this.setScheduleFromModel();
                    this.initSelectPicker();
                });
            },
            scheduleCaption(schedule) {
                return schedule.caption || `ID${schedule.id}`;
            },
            scheduleHtml(schedule) {
                return `
                <div class='subject-dropdown-option flex-left-full-width'>
                    <div class="labels full">
                        <h4><span class="line-clamp line-clamp-2">${this.scheduleCaption(schedule)}</span></h4>
                        <span class='small text-muted'>ID${schedule.id}</span>
                    </div>
                </div>
                `;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setScheduleFromModel() {
                if (this.value && this.schedules) {
                    this.chosenSchedule = this.schedules.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenSchedule = undefined;
                }
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
            },
        },
        computed: {
            schedulesForDropdown() {
                this.updateDropdownOptions();
                if (!this.schedules) {
                    return [];
                }
                this.$emit('update', this.schedules);
                return this.schedules;
            },
            selectOptions() {
                return {
                    noneSelectedText: this.$t('choose the schedule'),
                    liveSearchPlaceholder: this.$t('Search'),
                    noneResultsText: this.$t('No results match {0}'),
                };
            },
        },
        watch: {
            value() {
                this.setScheduleFromModel();
                this.updateDropdownOptions();
            },
            '$i18n.locale'() {
                $(this.$refs.dropdown).selectpicker('destroy');
                this.initSelectPicker();
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .select-loader {
        position: relative;
        text-align: center;
        .button-loading-dots {
            position: absolute;
            top: 8px;
            left: 50%;
            margin-left: -25px;
            z-index: 20;
        }
    }
</style>

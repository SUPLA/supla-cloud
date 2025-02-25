<template>
    <div>
        <select class="form-control hidden visible-xs mb-3"
            v-model="mobileWeekdayDisplay">
            <option
                v-for="(weekday, $index) in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                :value="$index + 1"
                :key="'mobileWeekday' + $index">
                {{ $t(weekday) }}
            </option>
        </select>
        <table :class="['week-schedule-selector', 'selection-mode-' + currentSelectionMode, 'mobile-weekday-' + mobileWeekdayDisplay]"
            v-if="temporaryModel">
            <thead>
            <tr>
                <th class="hour-header"></th>
                <!-- i18n: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
                <th v-for="(weekday, $index) in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                    :key="weekday"
                    :class="['hidden-xs weekday-header ellipsis', 'weekday-column-' + ($index + 1), {'current-hover': currentHover[0] === $index + 1, 'current-label': currentWeekday === $index + 1}]">
                    <span class="full-weekday-name">{{ $t(weekday) }}</span>
                    <span class="short-weekday-name">{{ shortWeekdayLabels[$index] }}</span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="hour in [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]"
                :key="hour">
                <th scope="row"
                    :class="['hour-header ellipsis', {'current-hover': currentHover[1] === hour, 'current-label': currentHour === hour}]">
                    {{ hourLabelStart(hour) }} - {{ hourLabelEnd(hour) }}
                </th>
                <td v-for="weekday in [1,2,3,4,5,6,7]"
                    :class="['hidden-xs', 'weekday-column-' + weekday, {'current-hover': currentHover[0] === weekday || currentHover[1] === hour}]"
                    :key="'0' + hour + weekday">
                    <div class="d-flex w-100">
                        <div :class="['time-slot flex-grow-1', `time-slot-mode-${temporaryModel[weekday][hour * quarterMultiplicator]}`]"
                            @mousedown="startSelection(weekday, hour * quarterMultiplicator)"
                            @mouseenter="expandSelection(weekday, hour * quarterMultiplicator)"
                            @mouseleave="clearCurrentHover()"
                            @mouseup="finishSelection()">
                            &nbsp;
                        </div>
                        <div :class="['time-slot flex-grow-1', `time-slot-mode-${temporaryModel[weekday][hour * 4 + 1]}`]"
                            v-if="quarters"
                            @mousedown="startSelection(weekday, hour * 4 + 1)"
                            @mouseenter="expandSelection(weekday, hour * 4 + 1)"
                            @mouseleave="clearCurrentHover()"
                            @mouseup="finishSelection()">
                            &nbsp;
                        </div>
                        <div :class="['time-slot flex-grow-1', `time-slot-mode-${temporaryModel[weekday][hour * 4 + 2]}`]"
                            v-if="quarters"
                            @mousedown="startSelection(weekday, hour * 4 + 2)"
                            @mouseenter="expandSelection(weekday, hour * 4 + 2)"
                            @mouseleave="clearCurrentHover()"
                            @mouseup="finishSelection()">
                            &nbsp;
                        </div>
                        <div :class="['time-slot flex-grow-1', `time-slot-mode-${temporaryModel[weekday][hour * 4 + 3]}`]"
                            v-if="quarters"
                            @mousedown="startSelection(weekday, hour * 4 + 3)"
                            @mouseenter="expandSelection(weekday, hour * 4 + 3)"
                            @mouseleave="clearCurrentHover()"
                            @mouseup="finishSelection()">
                            &nbsp;
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th></th>
                <th v-for="weekday in [1,2,3,4,5,6,7]"
                    :class="['hidden-xs text-center copy-buttons', 'weekday-column-' + weekday]"
                    :key="'copypaste' + weekday">
                    <a class="mx-1"
                        v-if="!copyFrom || copyFrom === weekday"
                        @click="copyFrom = (copyFrom === weekday ? undefined : weekday)">
                        <span :class="['pe-7s-copy-file', {'copy-from': copyFrom === weekday}]"></span>
                    </a>
                    <a class="mx-1"
                        @click="copyDay(copyFrom, weekday)"
                        v-if="copyFrom && copyFrom !== weekday">
                        <span class="pe-7s-paint"></span>
                    </a>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    import {cloneDeep} from "lodash";
    import {DateTime, Info} from "luxon";

    export default {
        components: {},
        props: {
            value: Object,
            quarters: Boolean,
            selectionMode: {
                type: [Number, Boolean],
                default: undefined,
            }
        },
        data() {
            return {
                model: undefined,
                temporaryModel: undefined,
                currentSelectionMode: undefined,
                selectionStartCoords: undefined,
                copyFrom: undefined,
                mouseUpCatcher: undefined,
                mobileWeekdayDisplay: 1,
                shortWeekdayLabels: Info.weekdays('short'),
                currentHover: [],
                currentHour: 0,
                currentWeekday: 0,
                currentTimeInterval: 0,
            };
        },
        mounted() {
            this.currentSelectionMode = this.selectionMode !== undefined ? this.selectionMode : 0;
            this.initModelFromValue();
            this.mouseUpCatcher = () => this.finishSelection();
            window.addEventListener('mouseup', this.mouseUpCatcher);
            this.getCurrentWeekdayHours();
            this.currentTimeInterval = setInterval(() => this.getCurrentWeekdayHours(), 60000);
        },
        beforeDestroy() {
            window.removeEventListener('mouseup', this.mouseUpCatcher);
            clearInterval(this.currentTimeInterval);
        },
        methods: {
            initModelFromValue() {
                this.model = {};
                const defaultValue = this.value && Object.keys(this.value).length ? 0 : 1;
                [...Array(7).keys()].forEach((weekday) => {
                    weekday += 1;
                    const hours = {};
                    [...Array(24 * this.quarterMultiplicator).keys()].forEach((hour) => {
                        hours[hour] = (this.value && this.value[weekday] && this.value[weekday][hour]) || defaultValue;
                    });
                    this.$set(this.model, weekday, hours);
                });
                this.temporaryModel = cloneDeep(this.model);
            },
            getCurrentWeekdayHours() {
                this.currentHour = DateTime.now().hour;
                this.currentWeekday = DateTime.now().weekday;
            },
            hourLabelStart(hour) {
                const start = DateTime.fromFormat(('0' + hour).substr(-2), 'HH');
                return start.toLocaleString(DateTime.TIME_SIMPLE);
            },
            hourLabelEnd(hour) {
                const end = DateTime.fromFormat(('0' + hour).substr(-2), 'HH').endOf('hour');
                return end.toLocaleString(DateTime.TIME_SIMPLE);
            },
            startSelection(weekday, hour) {
                if (this.selectionMode === false) {
                    return;
                }
                this.copyFrom = undefined;
                this.selectionStartCoords = {weekday, hour};
                if (this.selectionMode === undefined) {
                    this.currentSelectionMode = this.temporaryModel[weekday][hour] ? 0 : 1;
                } else {
                    this.currentSelectionMode = this.selectionMode;
                }
                this.expandSelection(weekday, hour);
            },
            expandSelection(weekday, hour) {
                if (this.selectionStartCoords) {
                    this.temporaryModel = cloneDeep(this.model);
                    const fromWeekday = Math.min(this.selectionStartCoords.weekday, weekday);
                    const toWeekday = Math.max(this.selectionStartCoords.weekday, weekday);
                    const fromHour = Math.min(this.selectionStartCoords.hour, hour);
                    const toHour = Math.max(this.selectionStartCoords.hour, hour);
                    for (let i = fromWeekday; i <= toWeekday; i++) {
                        for (let j = fromHour; j <= toHour; j++) {
                            this.temporaryModel[i][j] = this.currentSelectionMode;
                        }
                    }
                }
                this.currentHover = [weekday, Math.floor(hour / this.quarterMultiplicator)];
            },
            clearCurrentHover() {
                this.currentHover = [];
            },
            finishSelection() {
                if (this.selectionStartCoords) {
                    this.updateModel();
                    this.selectionStartCoords = undefined;
                }
            },
            updateModel() {
                this.model = this.temporaryModel;
                this.$emit('input', cloneDeep(this.model));
            },
            copyDay(copyFrom, copyTo) {
                this.temporaryModel[copyTo] = cloneDeep(this.temporaryModel[copyFrom]);
                this.updateModel();
            }
        },
        computed: {
            quarterMultiplicator() {
                return this.quarters ? 4 : 1;
            }
        },
        watch: {
            '$i18n.locale'() {
                this.shortWeekdayLabels = Info.weekdays('short');
            },
            value() {
                if (JSON.stringify(this.value) !== JSON.stringify(this.model)) {
                    this.initModelFromValue();
                }
            },
            selectionMode() {
                this.currentSelectionMode = this.selectionMode !== undefined ? this.selectionMode : 0;
            }
        }
    };
</script>

<style lang="scss">
    @import '../styles/variables';
    @import '../styles/mixins';

    table.week-schedule-selector {
        user-select: none;
        table-layout: fixed;
        width: 100%;

        @include on-xs-and-down {
            max-width: 90%;
        }

        .weekday-header {
            text-align: center;
        }
        .hour-header {
            text-align: right;
            padding-right: 5px;
            white-space: nowrap;
        }
        th {
            font-weight: normal;
        }
        tr {
            height: 1px; // allows for the links inside tds fill the whole height https://stackoverflow.com/a/34781198/878514
        }
        td {
            height: 100%;
            width: 100%;
            padding: 1px;
            vertical-align: middle;
            opacity: 0.8;
        }
        .time-slot {
            cursor: pointer;
            display: block;
            height: 100%;
            background: $supla-grey-light;
            border-left: 1px dotted #eee;
            @media (hover: hover) {
                &:hover {
                    background: darken($supla-grey-light, 10%);
                }
            }
            &:first-child {
                border-left: 0;
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            &:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
        }
        .copy-buttons {
            font-size: 1.3em;
            padding-top: .3em;
            a {
                color: $supla-grey-dark;
                .copy-from {
                    font-weight: bold;
                }
            }
        }
        @for $i from 1 through 7 {
            &.mobile-weekday-#{$i} {
                .hidden-xs.weekday-column-#{$i} {
                    display: table-cell !important;
                }
            }
        }
        .short-weekday-name {
            display: none;
        }

        th.current-hover {
            background: lighten($supla-yellow, 20%);
        }
        td.current-hover {
            opacity: 1;
        }
        .current-label {
            font-weight: bold;
        }
    }

    .narrow table.week-schedule-selector {
        .hour-header {
            width: 90px;
        }
        .short-weekday-name {
            display: inline;
        }
        .full-weekday-name {
            display: none;
        }
    }

    .mode-1-green table.week-schedule-selector {
        .time-slot.time-slot-mode-1 {
            background: $supla-green;
            @media (hover: hover) {
                &:hover {
                    background: lighten($supla-green, 20%);
                }
            }
        }
    }

    .selection-mode-false {
        .time-slot {
            cursor: default !important;
        }
    }
</style>

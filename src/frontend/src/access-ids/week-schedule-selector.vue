<template>
    <div>
        <table class="week-schedule-selector"
            v-if="temporaryModel">
            <thead>
            <tr>
                <th></th>
                <!-- i18n: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
                <th v-for="weekday in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                    :key="weekday"
                    class="weekday-header ellipsis">
                    {{ $t(weekday) }}
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="hour in [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]"
                :key="hour">
                <th scope="row"
                    class="hour-header ellipsis">
                    {{ hourLabel(hour) }}
                </th>
                <td v-for="weekday in [1,2,3,4,5,6,7]"
                    :key="'0' + hour + weekday">
                    <a :class="['time-slot', {'green': temporaryModel[weekday][hour]}]"
                        @mousedown="startSelection(weekday, hour)"
                        @mouseenter="expandSelection(weekday, hour)"
                        @mouseup="finishSelection()">
                        &nbsp;
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import moment from "moment";
    import {cloneDeep} from "lodash";

    export default {
        components: {},
        props: ['value'],
        data() {
            return {
                model: undefined,
                temporaryModel: undefined,
                selectionMode: undefined,
                selectionStartCoords: undefined,
            };
        },
        mounted() {
            this.model = {};
            [...Array(7).keys()].forEach((weekday) => {
                weekday += 1;
                const hours = {};
                [...Array(24).keys()].forEach((hour) => {
                    hours[hour] = (this.value && this.value[weekday] && this.value[weekday][hour]) || 0;
                });
                this.$set(this.model, weekday, hours);
            });
            this.temporaryModel = cloneDeep(this.model);
        },
        methods: {
            hourLabel(hour) {
                const start = moment(('0' + hour).substr(-2), 'HH');
                const end = moment(('0' + hour).substr(-2), 'HH').endOf('hour');
                return start.format('LT') + ' - ' + end.format('LT');
            },
            startSelection(weekday, hour) {
                this.selectionStartCoords = {weekday, hour};
                this.selectionMode = this.temporaryModel[weekday][hour] ? 0 : 1;
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
                            this.temporaryModel[i][j] = this.selectionMode;
                        }
                    }
                }
            },
            finishSelection() {
                this.updateModel();
                this.selectionStartCoords = undefined;
            },
            updateModel() {
                this.model = this.temporaryModel;
                this.$emit('input', cloneDeep(this.model));
            },
        },
        computed: {},
        watch: {}
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    table.week-schedule-selector {
        user-select: none;
        table-layout: fixed;
        width: 100%;
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
            height: inherit;
            padding: 1px;
        }
        .time-slot {
            display: block;
            height: 100%;
            background: $supla-grey-light;
            &:hover {
                background: lighten($supla-green, 40%);
            }
            &.green {
                background: $supla-green;
                &:hover {
                    background: lighten($supla-green, 20%);
                }
            }
        }
    }
</style>

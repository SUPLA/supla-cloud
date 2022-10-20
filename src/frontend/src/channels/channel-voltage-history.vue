<template>
    <div>
        <loading-cover :loading="!logs">
            <div class="container"
                v-if="logs">
                <div class="text-right mb-3 hidden">
                    <a :href="`/api/channels/${channel.id}/measurement-logs-csv?` | withDownloadAccessToken"
                        class="btn btn-default mx-1">{{ $t('Download the history of measurement') }}</a>
                    <button @click="deleteConfirm = true"
                        type="button"
                        class="btn btn-red ml-1">
                        <i class="pe-7s-trash"></i>
                        {{ $t('Delete measurement history') }}
                    </button>
                </div>
                <div class="text-center">
                    <label class="checkbox2">
                        <input type="checkbox"
                            v-model="daysWithIssuesOnly">
                        {{ $t('Show days with issues only') }}
                    </label>
                </div>
                <carousel
                    :navigation-enabled="true"
                    :pagination-enabled="false"
                    navigation-next-label="&gt;"
                    navigation-prev-label="&lt;"
                    :per-page-custom="[[1200, 10], [1024, 7], [600, 4], [1, 3]]"
                    ref="carousel">
                    <slide v-for="date in statsToShow"
                        :key="date.dayFormatted">
                        <a :class="[
                            'voltage-day-stat',
                            `gradient-${Math.floor(date.secTotal * 10 / maxSecTotal) * 10}`,
                            {selected: selectedDay === date.dayFormatted}
                            ]"
                            @click="selectDay(date)">
                            <p class="day">{{ date.day.toLocaleString({month: 'numeric', day: 'numeric'}) }}</p>
                            <p class="sec">{{ formatDuration(date.secTotal) }}</p>
                        </a>
                    </slide>
                </carousel>
                <div class="day-parts mb-3">
                    <a :class="[
                            'voltage-day-stat',
                            `gradient-${Math.floor(v.secTotal * 10 / maxSecTotalInSelectedDay) * 10}`,
                            {selected: selectedDayPart === $index}
                            ]"
                        @click="selectedDayPart = $index"
                        v-for="(v, $index) in selectedDayViolations"
                        :key="$index">
                        <p class="day">{{ v.label }}</p>
                        <p class="sec">
                            <span class="pe-7s-angle-up-circle"></span>
                            {{ formatDuration(v.secAboveTotal) }}
                        </p>
                        <p class="sec">
                            <span class="pe-7s-angle-down-circle"></span>
                            {{ formatDuration(v.secBelowTotal) }}
                        </p>
                    </a>
                </div>
                <div class="voltage-logs-list-container">
                    <div class="voltage-logs-list"
                        v-if="selectedDayViolations && selectedDayViolations[selectedDayPart].violations.length">
                        <div v-for="log in selectedDayViolations[selectedDayPart].violations"
                            :key="log.date_timestamp"
                            class="voltage-log panel panel-default">
                            <div class="panel-body">
                                <h4>
                                    {{ log.date_timestamp | formatDate('DATETIME_FULL_WITH_SECONDS') }} -
                                    {{ (log.date_timestamp + log.measurementTimeSec) | formatDate('DATETIME_FULL_WITH_SECONDS') }}
                                </h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <h4>{{ $t('Violation info') }}</h4>
                                        <dl class="dl-grid">
                                            <dt>{{ $t('Phase number') }}</dt>
                                            <dd>{{ log.phaseNo }}</dd>
                                            <dt>{{ $t('Measurement window') }}</dt>
                                            <dd>{{ log.measurementTimeSec }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Average voltage') }}</dt>
                                            <dd>{{ log.avgVoltage }} V</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <h4>
                                            <span v-if="log.countBelow > 0"
                                                class="violation-indicator text-danger">&bull;</span>
                                            {{ $t('Below threshold') }}
                                        </h4>
                                        <dl class="dl-grid">
                                            <dt>{{ $t('Violations count') }}</dt>
                                            <dd>{{ log.countBelow }}</dd>
                                            <dt>{{ $t('Total time') }}</dt>
                                            <dd>{{ log.secBelow }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Longest violation') }}</dt>
                                            <dd>{{ log.maxSecBelow }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Minimum voltage') }}</dt>
                                            <dd :class="{'text-danger text-bold': log.countBelow > 0}">{{ log.minVoltage }} V</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <h4>
                                            <span v-if="log.countAbove > 0"
                                                class="violation-indicator text-danger">&bull;</span>
                                            {{ $t('Above threshold') }}
                                        </h4>
                                        <dl class="dl-grid">
                                            <dt>{{ $t('Violations count') }}</dt>
                                            <dd>{{ log.countAbove }}</dd>
                                            <dt>{{ $t('Total time') }}</dt>
                                            <dd>{{ log.secAbove }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Longest violation') }}</dt>
                                            <dd>{{ log.maxSecAbove }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Maximum voltage') }}</dt>
                                            <dd :class="{'text-danger text-bold': log.countAbove > 0}">{{ log.maxVoltage }} V</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <empty-list-placeholder v-else/>
                </div>
            </div>
            <empty-list-placeholder v-if="logs && logs.length === 0"/>
        </loading-cover>
    </div>
</template>

<script>
    import {DateTime} from "luxon";
    import {Carousel, Slide} from 'vue-carousel';

    export default {
        components: {Carousel, Slide},
        props: {
            channel: Object,
        },
        data() {
            return {
                logs: undefined,
                stats: [],
                maxSecTotal: 600,
                maxSecTotalInSelectedDay: 600,
                selectedDay: undefined,
                selectedDayViolations: undefined,
                selectedDayPart: 0,
                daysWithIssuesOnly: false,
            };
        },
        beforeMount() {
            const afterTimestamp = Math.round(DateTime.now().minus({days: 30}).startOf('day').toSeconds());
            this.$http.get(`channels/${this.channel.id}/measurement-logs?logsType=voltage&limit=1000&order=ASC&afterTimestamp=${afterTimestamp}`)
                .then(({body: logItems}) => {
                    const dayStats = {};
                    this.logs = logItems.map(log => {
                        const date = DateTime.fromSeconds(log.date_timestamp);
                        log.date = date;
                        log.day = date.toFormat('ddMM');
                        if (!dayStats[log.day]) {
                            dayStats[log.day] = 0;
                        }
                        dayStats[log.day] += log.secTotal;
                        return log;
                    });
                    if (this.logs.length) {
                        const minDate = DateTime.fromSeconds(this.logs[0].date_timestamp).endOf('day');
                        for (let day = DateTime.now().endOf('day'); day >= minDate; day = day.minus({days: 1})) {
                            const secTotal = dayStats[day.toFormat('ddMM')] || 0;
                            const dayFormatted = day.toFormat('ddMM');
                            this.stats.push({day, dayFormatted, secTotal});
                            this.maxSecTotal = Math.max(this.maxSecTotal, secTotal);
                        }
                        this.selectDay(this.stats[0]);
                    }
                });
        },
        methods: {
            selectDay(stat) {
                this.selectedDay = stat.dayFormatted;
                const violations = this.logs.filter(log => log.day === stat.dayFormatted);
                this.selectedDayViolations = [];
                this.maxSecTotalInSelectedDay = 300;
                for (let dayPart = stat.day.startOf('day'); dayPart < stat.day.endOf('day'); dayPart = dayPart.plus({hours: 6})) {
                    const nextPart = dayPart.plus({hours: 6});
                    const violationsForPart = violations.filter(log => dayPart <= log.date && log.date < nextPart);
                    const secBelowTotal = violationsForPart.reduce((sum, log) => log.secBelow + sum, 0);
                    const secAboveTotal = violationsForPart.reduce((sum, log) => log.secAbove + sum, 0);
                    const label = dayPart.toLocaleString(DateTime.TIME_SIMPLE) + ' - ' + nextPart.minus({minutes: 1}).toLocaleString(DateTime.TIME_SIMPLE);
                    const secTotal = secBelowTotal + secAboveTotal;
                    this.maxSecTotalInSelectedDay = Math.max(this.maxSecTotalInSelectedDay, secTotal);
                    this.selectedDayViolations.push({label, violations: violationsForPart, secBelowTotal, secAboveTotal, secTotal});
                }
                this.selectedDayPart = 0;
            },
            formatDuration(duration) {
                return `${Math.floor(duration / 3600)}h ${Math.floor(((duration % 3600) / 60))}m ${Math.floor(duration % 60)}s`;
            },
        },
        computed: {
            statsToShow() {
                return this.daysWithIssuesOnly ? this.stats.filter(s => s.secTotal > 0) : this.stats;
            }
        }
    };
</script>

<style lang="scss">
    @use 'sass:list';
    @import '../styles/variables';

    .dl-grid {
        width: 100%;
        display: grid;
        grid-template-columns: max-content auto;
        dt, dd {
            display: inline-block;
            border-bottom: 1px solid $supla-grey-light;
            padding: 2px 0;
            &:last-of-type {
                border-bottom: 0;
            }
        }
        dt {
            padding-right: 10px;
            &:after {
                content: ':';
            }
        }
    }

    .voltage-logs-list-container {
        min-height: 500px;
    }

    .violation-indicator {
        font-size: 2em;
        line-height: 0.5em;
        vertical-align: middle;
    }

    .voltage-day-stat {
        border-radius: 5px;
        display: block;
        padding: 5px;
        color: $supla-black;
        text-align: center;
        transition: background-color .2s linear, border-color .2s linear;
        border: 2px solid $supla-grey-light;
        &:hover {
            color: $supla-black;
            border-color: $supla-grey-dark;
        }
        &.selected {
            border-color: $supla-black;
        }
        p {
            margin: 0;
            &.day {
                font-weight: bold;
            }
        }
    }

    .day-parts {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        .voltage-day-stat {
            flex: 1;
        }
    }

    $gradientColor: #ff851b;
    @for $i from 0 through 10 {
        .gradient-#{$i * 10} {
            background-color: lighten($gradientColor, 50% - ($i * 5%));
            &:hover {
                background-color: lighten($gradientColor, 55% - ($i * 5%));
            }
        }
    }
</style>

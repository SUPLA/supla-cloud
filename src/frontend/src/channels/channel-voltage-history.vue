<template>
    <div>
        <loading-cover :loading="!logs">
            <div class="container"
                v-if="logs && logs.length > 0">
                <div class="text-right mb-3">
                    <a :href="`/api/channels/${channel.id}/measurement-logs-csv?logsType=voltage&` | withDownloadAccessToken"
                        class="btn btn-default mx-1">{{ $t('Download the history of measurement') }}</a>
                    <div class="d-inline-block dropdown">
                        <button class="btn btn-danger dropdown-toggle btn-wrapped"
                            type="button"
                            data-toggle="dropdown">
                            {{ $t('Delete voltage aberrations history') }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li v-for="phaseNo in enabledPhases"
                                :key="phaseNo">
                                <a @click="deleteConfirm = phaseNo">
                                    {{ $t('Phase {phaseNumber}', {phaseNumber: phaseNo}) }}
                                </a>
                            </li>
                            <li>
                                <a @click="deleteConfirm = true">
                                    {{ $t('All events') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <label class="checkbox2">
                        <input type="checkbox"
                            v-model="daysWithIssuesOnly"
                            @change="selectVisibleStat()">
                        {{ $t('Hide days without aberrations') }}
                    </label>
                </div>
                <carousel
                    :navigation-enabled="true"
                    :pagination-enabled="false"
                    navigation-next-label="&gt;"
                    navigation-prev-label="&lt;"
                    :scroll-per-page="false"
                    :per-page-custom="[[1200, 10], [1024, 7], [600, 4], [1, 3]]"
                    @pageChange="loadMoreIfNeeded($event)"
                    ref="carousel">
                    <slide v-for="date in statsToShow"
                        :key="date.dayFormatted">
                        <a v-if="date !== statsToShow[0] || !fetching"
                            :class="[
                            'voltage-day-stat',
                            `gradient-${chooseGradient(date.secTotalAboveOrBelow, maxSecTotal)}`,
                            {selected: selectedDay === date.dayFormatted}
                            ]"
                            @click="selectDay(date)">
                            <p class="day">{{ date.day.toLocaleString({month: 'numeric', day: 'numeric'}) }}</p>
                            <p class="sec" v-if="date.secTotalAboveOrBelow" :title="$t('Number of aberrations')">
                                <span class="glyphicon glyphicon-arrow-down below-threshold-indicator"></span> {{ date.countBelowTotal }}
                                <span class="glyphicon glyphicon-arrow-up above-threshold-indicator"></span> {{ date.countAboveTotal }}
                            </p>
                            <p class="sec" v-else><span class="glyphicon glyphicon-ok-sign text-success"></span></p>
                        </a>
                        <div v-else class="text-center pt-3">
                            <button-loading-dots/>
                        </div>
                    </slide>
                </carousel>
                <div class="day-parts mb-3">
                    <a :class="[
                            'voltage-day-stat',
                            `gradient-${chooseGradient(v.secTotalAboveOrBelow, maxSecTotalInSelectedDay)}`,
                            {selected: selectedDayPart === $index}
                            ]"
                        @click="selectDayPart($index)"
                        v-for="(v, $index) in selectedDayViolations"
                        :key="$index">
                        <p class="day">{{ v.label }}</p>
                        <p class="sec" v-if="v.secTotalAboveOrBelow" :title="$t('Number of aberrations')">
                            <span class="glyphicon glyphicon-arrow-down below-threshold-indicator"></span> {{ v.countBelowTotal }}
                            <span class="glyphicon glyphicon-arrow-up above-threshold-indicator"></span> {{ v.countAboveTotal }}
                        </p>
                        <p class="sec" v-else><span class="glyphicon glyphicon-ok-sign text-success"></span></p>
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
                                    {{ (log.date_timestamp - log.measurementTimeSec) | formatDate('TIME_WITH_SECONDS') }} &mdash;
                                    {{ log.date_timestamp | formatDate('TIME_WITH_SECONDS') }}
                                </h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <h4>{{ $t('Aberration info') }}</h4>
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
                                                class="glyphicon glyphicon-arrow-down below-threshold-indicator"></span>
                                            {{ $t('Below threshold') }}
                                        </h4>
                                        <dl class="dl-grid">
                                            <dt>{{ $t('Aberrations count') }}</dt>
                                            <dd>{{ log.countBelow }}</dd>
                                            <dt>{{ $t('Total time') }}</dt>
                                            <dd>{{ log.secBelow }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Longest aberration') }}</dt>
                                            <dd>{{ log.maxSecBelow }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Minimum voltage') }}</dt>
                                            <dd :class="{'text-danger text-bold': log.countBelow > 0}">{{ log.minVoltage }} V</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <h4>
                                            <span v-if="log.countAbove > 0"
                                                class="glyphicon glyphicon-arrow-up above-threshold-indicator"></span>
                                            {{ $t('Above threshold') }}
                                        </h4>
                                        <dl class="dl-grid">
                                            <dt>{{ $t('Aberrations count') }}</dt>
                                            <dd>{{ log.countAbove }}</dd>
                                            <dt>{{ $t('Total time') }}</dt>
                                            <dd>{{ log.secAbove }} {{ $t('sec.') }}</dd>
                                            <dt>{{ $t('Longest aberration') }}</dt>
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
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteMeasurements()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete the events from the voltage aberrations history?')">
            <p v-if="deleteConfirm === true">
                {{ $t('The entire history of the voltage aberrations will be removed from this channel.') }}
            </p>
            <p v-else>
                {{ $t('The entire history of the voltage aberrations from phase {phaseNumber} will be removed from this channel.', {phaseNumber: deleteConfirm}) }}
            </p>
        </modal-confirm>
    </div>
</template>

<script>
    import {DateTime} from "luxon";
    import {Carousel, Slide} from 'vue-carousel';
    import {successNotification} from "@/common/notifier";

    export default {
        components: {Carousel, Slide},
        props: {
            channel: Object,
        },
        data() {
            return {
                loadedLogsCount: 0,
                logs: [],
                stats: [],
                maxSecTotal: 600,
                maxSecTotalInSelectedDay: 600,
                selectedDay: undefined,
                selectedDayViolations: undefined,
                selectedDayPart: 0,
                daysWithIssuesOnly: false,
                deleteConfirm: false,
                allLoaded: false,
                fetching: false,
                beforeTimestamp: Math.round(DateTime.now().toSeconds()),
            };
        },
        mounted() {
            this.fetchLogs();
        },
        methods: {
            fetchLogs() {
                this.fetching = true;
                return this.$http.get(`channels/${this.channel.id}/measurement-logs?logsType=voltage&limit=1000&order=DESC&offset=${this.loadedLogsCount}&behforeTimestamp=${this.beforeTimestamp}`)
                    .then(({body: logItems}) => {
                        this.allLoaded = logItems.length < 1000;
                        this.loadedLogsCount += 1000;
                        const newLogs = logItems
                            .reverse()
                            .filter(({phaseNo}) => this.enabledPhases.includes(phaseNo))
                            .map(log => {
                                const date = DateTime.fromSeconds(log.date_timestamp);
                                log.date = date;
                                log.day = date.toFormat('ddMM');
                                return log;
                            });
                        this.logs.unshift(...newLogs);
                        const dayStats = {};
                        this.logs.forEach((log) => {
                            if (!dayStats[log.day]) {
                                dayStats[log.day] = {secTotalAboveOrBelow: 0, countBelowTotal: 0, countAboveTotal: 0};
                            }
                            dayStats[log.day].secTotalAboveOrBelow += log.secBelow;
                            dayStats[log.day].secTotalAboveOrBelow += log.secAbove;
                            dayStats[log.day].countBelowTotal += log.countBelow;
                            dayStats[log.day].countAboveTotal += log.countAbove;
                        });
                        if (this.logs.length) {
                            this.stats = [];
                            const minDate = DateTime.fromSeconds(this.logs[0].date_timestamp).endOf('day');
                            for (let day = minDate; day <= DateTime.now().endOf('day'); day = day.plus({days: 1})) {
                                const secTotalAboveOrBelow = dayStats[day.toFormat('ddMM')]?.secTotalAboveOrBelow || 0;
                                const countBelowTotal = dayStats[day.toFormat('ddMM')]?.countBelowTotal || 0;
                                const countAboveTotal = dayStats[day.toFormat('ddMM')]?.countAboveTotal || 0;
                                const dayFormatted = day.toFormat('ddMM');
                                this.stats.push({day, dayFormatted, secTotalAboveOrBelow, countBelowTotal, countAboveTotal});
                                this.maxSecTotal = Math.max(this.maxSecTotal, secTotalAboveOrBelow);
                            }
                            if (this.$route.query.day) {
                                this.selectDay(this.$route.query.day);
                            } else {
                                this.selectDay('');
                            }
                        }
                    })
                    .finally(() => {
                        this.fetching = false;
                        if (this.stats.length && this.selectedDay === this.stats[0].dayFormatted) {
                            this.loadMoreIfNeeded(0);
                        }
                    });
            },
            loadMoreIfNeeded(page) {
                if (page === 0 && !this.allLoaded && !this.fetching) {
                    this.$refs.carousel.dragging = true;
                    this.selectDay(this.stats[0]);
                    this.fetchLogs()
                        .finally(() => setTimeout(() => this.$refs.carousel.dragging = false));
                }
            },
            selectDay(stat) {
                if (typeof stat === 'string') {
                    const index = this.statsToShow.map(stat => stat.dayFormatted).indexOf(stat);
                    stat = index === -1 ? this.statsToShow[this.statsToShow.length - 1] : this.statsToShow[index];
                }
                this.selectedDay = stat.dayFormatted;
                const violations = this.logs.filter(log => log.day === stat.dayFormatted);
                this.selectedDayViolations = [];
                this.maxSecTotalInSelectedDay = 300;
                for (let dayPart = stat.day.startOf('day'); dayPart < stat.day.endOf('day'); dayPart = dayPart.plus({hours: 6})) {
                    const nextPart = dayPart.plus({hours: 6});
                    const violationsForPart = violations.filter(log => dayPart <= log.date && log.date < nextPart);
                    const countBelowTotal = violationsForPart.reduce((sum, log) => log.countBelow + sum, 0);
                    const countAboveTotal = violationsForPart.reduce((sum, log) => log.countAbove + sum, 0);
                    const label = dayPart.toLocaleString(DateTime.TIME_SIMPLE) + ' - ' + nextPart.minus({minutes: 1}).toLocaleString(DateTime.TIME_SIMPLE);
                    const secTotalAboveOrBelow = violationsForPart.reduce((sum, log) => log.secBelow + log.secAbove + sum, 0);
                    this.maxSecTotalInSelectedDay = Math.max(this.maxSecTotalInSelectedDay, secTotalAboveOrBelow);
                    this.selectedDayViolations.push({
                        label,
                        violations: violationsForPart,
                        countBelowTotal,
                        countAboveTotal,
                        secTotalAboveOrBelow
                    });
                }
                this.selectDayPart();
                const index = this.statsToShow.map(stat => stat.dayFormatted).indexOf(this.selectedDay);
                if (index !== -1) {
                    setTimeout(() => {
                        let desiredPage = index - this.$refs.carousel.perPage + 2;
                        desiredPage = Math.max(0, Math.min(this.$refs.carousel.pageCount - 1, desiredPage));
                        this.$refs.carousel.goToPage(desiredPage);
                    });
                }
            },
            selectDayPart(index = undefined) {
                if (index === undefined) {
                    index = Math.max(0, Math.min(4, +this.$route.query.part));
                    if (![0, 1, 2, 3].includes(index)) {
                        index = 0;
                    }
                }
                this.selectedDayPart = index;
                if (this.selectedDay !== this.$route.query.day || this.selectedDayPart !== +this.$route.query.part) {
                    const query = {...this.$route.query, day: this.selectedDay, part: +this.selectedDayPart};
                    if (this.$route.query.day) {
                        this.$router.push({query});
                    } else {
                        this.$router.replace({query});
                    }
                }
            },
            selectVisibleStat() {
                this.selectDay(this.selectedDay);
            },
            deleteMeasurements() {
                const phaseToDelete = this.deleteConfirm === true ? '' : this.deleteConfirm;
                this.deleteConfirm = false;
                this.$http.delete(`channels/${this.channel.id}/measurement-logs?logsType=voltage&phase=${phaseToDelete}`)
                    .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')))
                    .then(() => {
                        this.logs = undefined;
                        this.stats = [];
                        this.selectedDayViolations = [];
                        this.selectedDay = undefined;
                        this.selectedDayPart = 0;
                        this.loadedLogsCount = 0;
                        return this.fetchLogs();
                    });
            },
            chooseGradient(amount, maximum) {
                if (amount > 0) {
                    return Math.max(10, Math.floor(amount * 10 / maximum) * 10);
                } else {
                    return 0;
                }
            }
        },
        computed: {
            statsToShow() {
                return this.daysWithIssuesOnly ? this.stats.filter(s => s.secTotalAboveOrBelow > 0) : this.stats;
            },
            enabledPhases() {
                return this.channel.config.enabledPhases || [];
            },
        },
        watch: {
            '$route.query.day'() {
                this.selectDay(this.$route.query.day);
            },
            '$route.query.part'() {
                this.selectDay(this.$route.query.day);
            },
        },
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
    @for $i from 1 through 10 {
        .gradient-#{$i * 10} {
            background-color: lighten($gradientColor, 42% - ($i * 4.2%));
            &:hover {
                background-color: lighten($gradientColor, 47% - ($i * 4.2%));
            }
        }
    }

    .below-threshold-indicator {
        color: #1b6d85;
    }

    .above-threshold-indicator {
        color: #761c19;
    }
</style>

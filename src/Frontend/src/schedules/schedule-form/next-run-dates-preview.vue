<template>
    <div>
        <div class="text-center"
            v-if="value.fetchingNextRunDates">
            <button-loading-dots></button-loading-dots>
        </div>
        <div class="forum-group"
            v-if="value.length > 0"
            :class="{opacity60: value.fetching}">
            <h4>{{ $t('The next available executions') }}</h4>
            <div class="list-group">
                <div class="list-group-item"
                    v-for="nextRunDate in humanizedNextRunDates">
                    <span class="pull-right small text-muted">
                        {{ nextRunDate.fromNow }}
                    </span>
                    {{ nextRunDate.date }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    import ButtonLoadingDots from "../../common/gui/loaders/button-loading-dots.vue";
    import Vue from "vue";

    export default {
        props: ['value', 'schedule'],
        components: {ButtonLoadingDots},
        computed: {
            humanizedNextRunDates() {
                return this.value.map(function (dateString) {
                    return {
                        date: moment(dateString).format('LLL'),
                        fromNow: moment(dateString).fromNow()
                    };
                });
            },
            nextRunDatesQuery() {
                return {
                    mode: this.schedule.mode,
                    timeExpression: this.schedule.timeExpression,
                    dateStart: this.schedule.mode == 'once' ? undefined : this.schedule.dateStart,
                    dateEnd: this.schedule.mode == 'once' ? undefined : this.schedule.dateEnd
                };
            }
        },
        mounted() {
            this.fetchNextRunDates();
        },
        methods: {
            fetchNextRunDates() {
                const query = this.nextRunDatesQuery;
                if (!query.timeExpression) {
                    this.$emit('input', []);
                } else {
                    this.$set(this.value, 'fetching', true);
                    Vue.http.post('schedules/next-run-dates', query)
                        .then(({body: nextRunDates}) => {
                            this.$emit('input', nextRunDates);
                            if (query != this.nextRunDatesQuery) {
                                this.fetchNextRunDates();
                            }
                        })
                        .catch(() => this.$emit('input', []));
                }
            },
        },
        watch: {
            'schedule.timeExpression'() {
                this.fetchNextRunDates();
            },
            'schedule.mode'() {
                this.fetchNextRunDates();
            },
            'schedule.dateStart'() {
                this.fetchNextRunDates();
            },
            'schedule.dateEnd'() {
                this.fetchNextRunDates();
            },
        }
    };
</script>

<style>
    .opacity60 {
        opacity: .6;
    }
</style>

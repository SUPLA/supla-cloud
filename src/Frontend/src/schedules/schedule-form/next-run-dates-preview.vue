<template>
    <div>
        <div class="progress"
            v-if="fetchingNextRunDates">
            <div class="progress-bar progress-bar-info progress-bar-striped active"
                style="width: 100%">
                {{ $t('calculating the next available executions') }}...
            </div>
        </div>
        <div class="forum-group"
            v-if="nextRunDates.length > 0"
            :class="{opacity60: fetchingNextRunDates}">
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

<script type="text/babel">
    import {mapState} from "vuex";
    import moment from "moment";

    export default {
        name: 'next-run-dates-preview',
        computed: {
            humanizedNextRunDates() {
                return this.nextRunDates.map(function (dateString) {
                    return {
                        date: moment(dateString).format('LLL'),
                        fromNow: moment(dateString).fromNow()
                    };
                });
            },
            ...mapState(['fetchingNextRunDates', 'nextRunDates'])
        }
    };
</script>

<style>
    .opacity60 {
        opacity: .6;
    }
</style>
